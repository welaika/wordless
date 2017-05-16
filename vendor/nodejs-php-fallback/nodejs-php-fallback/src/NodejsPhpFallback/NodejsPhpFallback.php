<?php

namespace NodejsPhpFallback;

use Composer\Composer;
use Composer\Json\JsonFile;
use Composer\Script\Event;

class NodejsPhpFallback
{
    protected $nodePath;

    protected static $modulePaths = array();

    protected static $maxInstallRetry = 3;

    public function __construct($nodePath = null)
    {
        $this->nodePath = $nodePath ?: 'node';
    }

    public function setNodePath($nodePath)
    {
        $this->nodePath = $nodePath;

        return $this;
    }

    public function getNodePath()
    {
        return $this->nodePath;
    }

    protected function checkFallback($fallback)
    {
        if ($this->isNodeInstalled()) {
            return true;
        }

        if (is_null($fallback)) {
            throw new \ErrorException('Please install node.js or provide a PHP fallback.', 2);
        }

        if (!is_callable($fallback)) {
            throw new \InvalidArgumentException('The fallback provided is not callable.', 1);
        }

        return false;
    }

    protected function shellExec($withNode)
    {
        $prefix = $withNode ? $this->getNodePath() . ' ' : '';

        return function ($script) use ($prefix) {
            return shell_exec($prefix . $script . ' 2>&1');
        };
    }

    protected function execOrFallback($script, $fallback, $withNode)
    {
        $exec = $this->checkFallback($fallback)
            ? $this->shellExec($withNode)
            : $fallback;

        return call_user_func($exec, $script);
    }

    public function isNodeInstalled()
    {
        $exec = $this->shellExec(true);

        return substr($exec('--version'), 0, 1) === 'v';
    }

    public function exec($script, $fallback = null)
    {
        return $this->execOrFallback($script, $fallback, false);
    }

    public function nodeExec($script, $fallback = null)
    {
        return $this->execOrFallback($script, $fallback, true);
    }

    public function execModuleScript($module, $script, $arguments, $fallback = null)
    {
        return $this->nodeExec(
            static::getModuleScript($module, $script) . (empty($arguments) ? '' : ' ' . $arguments),
            $fallback
        );
    }

    public static function setModulePath($module, $path)
    {
        static::$modulePaths[$module] = $path;
    }

    public static function setMaxInstallRetry($count)
    {
        static::$maxInstallRetry = $count;
    }

    public static function getPrefixPath()
    {
        return dirname(dirname(__DIR__));
    }

    public static function getNodeModules()
    {
        return static::getPrefixPath() . DIRECTORY_SEPARATOR . 'node_modules';
    }

    public static function getNodeModule($module)
    {
        return empty(static::$modulePaths[$module])
            ? static::getNodeModules() . DIRECTORY_SEPARATOR . $module
            : static::$modulePaths[$module];
    }

    public static function getModuleScript($module, $script)
    {
        $module = static::getNodeModule($module);
        $path = $module . DIRECTORY_SEPARATOR . $script;
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("The $script was not found in the module path $module.", 3);
        }

        return escapeshellarg(realpath($path));
    }

    protected static function appendConfig(&$npm, $directory)
    {
        $json = new JsonFile($directory . DIRECTORY_SEPARATOR . 'composer.json');
        try {
            $dependencyConfig = $json->read();
        } catch (\RuntimeException $e) {
            $dependencyConfig = null;
        }
        if (is_array($dependencyConfig) && isset($dependencyConfig['extra'], $dependencyConfig['extra']['npm'])) {
            $npm = array_merge($npm, (array) $dependencyConfig['extra']['npm']);
        }
    }

    protected static function getNpmConfig(Composer $composer)
    {
        $vendorDir = $composer->getConfig()->get('vendor-dir');

        $npm = array();

        foreach (scandir($vendorDir) as $namespace) {
            if ($namespace === '.' || $namespace === '..' || !is_dir($directory = $vendorDir . DIRECTORY_SEPARATOR . $namespace)) {
                continue;
            }
            foreach (scandir($directory) as $dependency) {
                if ($dependency === '.' || $dependency === '..' || !is_dir($subDirectory = $directory . DIRECTORY_SEPARATOR . $dependency)) {
                    continue;
                }
                static::appendConfig($npm, $subDirectory);
            }
        }
        static::appendConfig($npm, dirname($vendorDir));

        return $npm;
    }

    public static function install(Event $event)
    {
        $composer = $event->getComposer();
        $npm = static::getNpmConfig($composer);

        if (!count($npm)) {
            $config = $composer->getPackage()->getExtra();
            $event->getIO()->write(isset($config['npm'])
                ? 'No packages found.'
                : "Warning: in order to use NodejsPhpFallback, you should add a 'npm' setting in your composer.json"
            );

            return;
        }

        $packages = '';
        foreach ($npm as $package => $version) {
            if (is_int($package)) {
                $package = $version;
                $version = '*';
            }
            $install = $package . '@"' . addslashes($version) . '"';
            $event->getIO()->write('Package found added to be installed with npm: ' . $install);
            $packages .= ' ' . $install;
        }

        for ($i = static::$maxInstallRetry; $i > 0; $i--) {
            $result = shell_exec(
                'npm install --loglevel=error ' .
                '--prefix ' . escapeshellarg(static::getPrefixPath()) .
                $packages .
                ' 2>&1'
            );

            if (strpos($result, 'npm ERR!') === false) {
                $event->getIO()->write('Packages installed.');

                return;
            }
        }

        $event->getIO()->writeError('Installation failed after ' . static::$maxInstallRetry . ' tries.');
    }
}
