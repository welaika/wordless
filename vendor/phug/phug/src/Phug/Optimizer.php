<?php

namespace Phug;

use Phug\Compiler\Locator\FileLocator;

class Optimizer
{
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

    /**
     * Return a hashed print from input file or content.
     *
     * @param string $input
     *
     * @return string
     */
    private function hashPrint($input)
    {
        // Get the stronger hashing algorithm available to minimize collision risks
        $algorithms = hash_algos();
        $algorithm = $algorithms[0];
        $number = 0;
        foreach ($algorithms as $hashAlgorithm) {
            $lettersLength = substr($hashAlgorithm, 0, 2) === 'md'
                ? 2
                : (substr($hashAlgorithm, 0, 3) === 'sha'
                    ? 3
                    : 0
                );
            if ($lettersLength) {
                $hashNumber = substr($hashAlgorithm, $lettersLength);
                if ($hashNumber > $number) {
                    $number = $hashNumber;
                    $algorithm = $hashAlgorithm;
                }

                continue;
            }
        }

        return rtrim(strtr(base64_encode(hash($algorithm, $input, true)), '+/', '-_'), '=');
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

    public function __construct(array $options = [])
    {
        $this->locator = new FileLocator();
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
        return $this->locator->locate(
            $file,
            $this->paths,
            isset($this->options['extensions'])
                ? $this->options['extensions']
                : ['', '.pug', '.jade']
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
            return false;
        }

        if (!$this->cacheDirectory) {
            return true;
        }

        $sourcePath = $this->resolve($file);
        $cachePath = rtrim($this->cacheDirectory, '\\/').DIRECTORY_SEPARATOR.$this->hashPrint($sourcePath).'.php';

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

            throw new \RuntimeException(
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

        extract($__pug_parameters);
        include $__pug_cache_file;
    }

    /**
     * Returns a rendered template file.
     *
     * @param string $file
     * @param array  $parameters
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
}
