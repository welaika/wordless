<?php

namespace Phug;

use Phug\Compiler\Locator\FileLocator;
use Phug\Renderer\Partial\RegistryTrait;
use Phug\Util\Partial\HashPrintTrait;
use RuntimeException;

class Optimizer
{
    use HashPrintTrait;
    use RegistryTrait;

    /**
     * Facade for rendering fallback.
     *
     * @const string
     */
    const FACADE = Phug::class;

    /**
     * Rendering options.
     *
     * @var array
     */
    private $options;

    /**
     * Templates directories.
     *
     * @var array
     */
    private $paths;

    /**
     * Cache directory.
     *
     * @var string
     */
    private $cacheDirectory;

    /**
     * Locator to resolve template file paths.
     *
     * @var FileLocator
     */
    private $locator;

    public function __construct(array $options = [])
    {
        $this->paths = isset($options['paths']) ? $options['paths'] : [];
        if (isset($options['base_dir'])) {
            $this->paths[] = $options['base_dir'];
            unset($options['base_dir']);
            $options['paths'] = $this->paths;
        }
        if (isset($options['basedir'])) {
            $this->paths[] = $options['basedir'];
            unset($options['basedir']);
            $options['paths'] = $this->paths;
        }
        if (isset($options['cache']) && !isset($options['cache_dir'])) {
            $options['cache_dir'] = $options['cache'];
        }
        $this->options = $options;
        $this->cacheDirectory = isset($options['cache_dir']) ? $options['cache_dir'] : '';
    }

    /**
     * Resolve a template file path.
     *
     * @param string $file
     *
     * @return bool|null|string
     */
    public function resolve($file)
    {
        return $this->getLocator()->locate(
            $file,
            $this->paths,
            $this->getExtensions()
        );
    }

    /**
     * Returns true is a template file is expired, false else.
     * $cachePath will be set with the template cache file path.
     *
     * @param string $file
     * @param string &$cachePath
     *
     * @return bool
     */
    public function isExpired($file, &$cachePath = null)
    {
        if (isset($this->options['up_to_date_check']) && !$this->options['up_to_date_check']) {
            if (func_num_args() > 1) {
                $cachePath = $this->getRegistryPath($file);

                if (!$cachePath) {
                    list(, $cachePath) = $this->getSourceAndCachePaths($file);
                }
            }

            return false;
        }

        if (!$this->cacheDirectory) {
            return true;
        }

        list($sourcePath, $cachePath) = $this->getSourceAndCachePaths($file);

        if (!file_exists($cachePath)) {
            return true;
        }

        return $this->hasExpiredImport($sourcePath, $cachePath);
    }

    /**
     * Display a template.
     *
     * @param string $__pug_file
     * @param array  $__pug_parameters
     */
    public function displayFile($__pug_file, array $__pug_parameters = [])
    {
        if ($this->isExpired($__pug_file, $__pug_cache_file)) {
            if (isset($this->options['render'])) {
                call_user_func($this->options['render'], $__pug_file, $__pug_parameters, $this->options);

                return;
            }

            if (isset($this->options['renderer'])) {
                $this->options['renderer']->displayFile($__pug_file, $__pug_parameters);

                return;
            }

            if (isset($this->options['renderer_class_name'])) {
                $className = $this->options['renderer_class_name'];
                $renderer = new $className($this->options);
                $renderer->displayFile($__pug_file, $__pug_parameters);

                return;
            }

            $facade = isset($this->options['facade'])
                ? $this->options['facade']
                : static::FACADE;

            if (is_callable([$facade, 'displayFile'])) {
                $facade::displayFile($__pug_file, $__pug_parameters, $this->options);

                return;
            }

            throw new RuntimeException(
                'No valid render method, renderer engine, renderer class or facade provided.'
            );
        }

        if (isset($this->options['shared_variables'])) {
            $__pug_parameters = array_merge($this->options['shared_variables'], $__pug_parameters);
        }

        if (isset($this->options['globals'])) {
            $__pug_parameters = array_merge($this->options['globals'], $__pug_parameters);
        }

        if (isset($this->options['self']) && $this->options['self']) {
            $self = $this->options['self'] === true ? 'self' : strval($this->options['self']);
            $__pug_parameters = [$self => $__pug_parameters];
        }

        $execution = function () use ($__pug_cache_file, &$__pug_parameters) {
            extract($__pug_parameters);
            include $__pug_cache_file;
        };

        if (isset($__pug_parameters['this'])) {
            $execution = $execution->bindTo($__pug_parameters['this']);
            unset($__pug_parameters['this']);
        }

        $execution();
    }

    /**
     * Returns a rendered template file.
     *
     * @param string $file       file path
     * @param array  $parameters local variables
     *
     * @return string
     */
    public function renderFile($file, array $parameters = [])
    {
        ob_start();
        $this->displayFile($file, $parameters);

        return ob_get_clean();
    }

    /**
     * Call an optimizer method statically.
     *
     * @param string $name      method name
     * @param array  $arguments method argument to be passed
     * @param array  $options   options the optimizer will be created with
     *
     * @return mixed
     */
    public static function call($name, array $arguments = [], array $options = [])
    {
        return call_user_func_array([new static($options), $name], $arguments);
    }

    /**
     * Returns [sourcePath, cachePath] for a given file.
     *
     * @param string $file
     *
     * @return [string, string]
     */
    protected function getSourceAndCachePaths($file)
    {
        $sourcePath = $this->resolve($file);
        $cachePath = $this->getCachePath($this->hashPrint($sourcePath));

        return [$sourcePath, $cachePath];
    }

    /**
     * Get list of extensions to try.
     *
     * @return string[]
     */
    protected function getExtensions()
    {
        return isset($this->options['extensions'])
            ? $this->options['extensions']
            : ['', '.pug', '.jade'];
    }

    /**
     * Lazy loaded the file locator.
     *
     * @return FileLocator
     */
    private function getLocator()
    {
        if (!$this->locator) {
            $this->locator = new FileLocator();
        }

        return $this->locator;
    }

    /**
     * Returns true if the path has an expired imports linked.
     *
     * @param $path
     *
     * @return bool
     */
    private function hasExpiredImport($sourcePath, $cachePath)
    {
        $importsMap = $cachePath.'.imports.serialize.txt';

        if (!file_exists($importsMap)) {
            return true;
        }

        $importPaths = unserialize(file_get_contents($importsMap)) ?: [];
        $importPaths[] = $sourcePath;
        $time = filemtime($cachePath);
        foreach ($importPaths as $importPath) {
            if (!file_exists($importPath) || filemtime($importPath) >= $time) {
                // If only one file has changed, expires
                return true;
            }
        }

        // If only no files changed, it's up to date
        return false;
    }

    /**
     * Get PHP cached file for a given name.
     *
     * @param string $name
     *
     * @return string
     */
    private function getCachePath($name)
    {
        return $this->getRawCachePath($name.'.php');
    }

    /**
     * Get PHP cached file for a given file basename.
     *
     * @param string $file)
     *
     * @return string
     */
    private function getRawCachePath($file)
    {
        return rtrim($this->cacheDirectory, '\\/').DIRECTORY_SEPARATOR.$file;
    }

    /**
     * Get path from the cache registry (if up_to_date_check set to false only).
     *
     * @param string $path required view path.
     *
     * @return string|false false if no path registred, the path else.
     */
    private function getRegistryPath($path)
    {
        $cachePath = $this->findCachePathInRegistryFile(
            $path,
            $this->getCachePath('registry'),
            $this->getExtensions()
        );

        if ($cachePath) {
            return $this->getRawCachePath($cachePath);
        }

        return false;
    }
}
