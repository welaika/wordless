<?php

namespace Phug\Renderer\Adapter;

use Phug\Compiler\LocatorInterface;
use Phug\Renderer;
use Phug\Renderer\AbstractAdapter;
use Phug\Renderer\CacheInterface;
use Phug\Renderer\Partial\FileAdapterCacheToolsTrait;
use Phug\Renderer\Partial\RegistryTrait;
use Phug\Renderer\Partial\RenderingFileTrait;
use Phug\Renderer\Task\TasksGroup;
use Phug\Util\Partial\HashPrintTrait;
use RuntimeException;

/**
 * Renderer using files system.
 *
 * Options to customize paths:
 * - cache_dir directory to save the rendered files (no cache by default)
 * - tmp_dir working directory (directory used to temporarily save files if no long term cache_dir is provided,
 *   sys_get_temp_dir() by default)
 * - tmp_name_function function used to create temporary files (tempnam() by default)
 * - up_to_date_check (true: check if templates changed since the cached file was written, false: cache can only be
 *   cleared manually)
 * - keep_base_name (true: file name of the template will appear in the cached file name, false: only a hash is used
 *   in the cached file)
 */
class FileAdapter extends AbstractAdapter implements CacheInterface, LocatorInterface
{
    use FileAdapterCacheToolsTrait;
    use HashPrintTrait;
    use RegistryTrait;
    use RenderingFileTrait;

    public function __construct(Renderer $renderer, $options)
    {
        parent::__construct($renderer, [
            'cache_dir'         => null,
            'tmp_dir'           => sys_get_temp_dir(),
            'tmp_name_function' => 'tempnam',
            'up_to_date_check'  => true,
            'keep_base_name'    => false,
        ]);

        $this->setOptions($options);
    }

    /**
     * Return the cached file path after cache optional process.
     *
     * @param string   $path     pug file
     * @param string   $input    pug input code
     * @param callable $rendered method to compile the source into PHP
     * @param &bool    $success  reference to a variable to be set to true/false on success/failure
     *
     * @return string
     */
    public function cache($path, $input, callable $rendered, &$success = null)
    {
        $cacheFolder = $this->getCacheDirectory();
        $destination = $path;

        if (!$this->isCacheUpToDate($destination, $input)) {
            if (!is_writable($cacheFolder)) {
                throw new RuntimeException(sprintf('Cache directory must be writable. "%s" is not.', $cacheFolder), 6);
            }

            $compiler = $this->getRenderer()->getCompiler();
            $fullPath = $compiler->locate($path) ?: $path;
            $output = $rendered($fullPath, $input);
            $importsPaths = $compiler->getImportPaths($fullPath);

            $success = $this->cacheFileContents(
                $destination,
                $output,
                $importsPaths
            );
        }

        return $destination;
    }

    /**
     * Display rendered template after optional cache process.
     *
     * @param string   $path      pug file
     * @param string   $input     pug input code
     * @param callable $rendered  method to compile the source into PHP
     * @param array    $variables local variables
     * @param &bool    $success   reference to a variable to be set to true/false on success/failure
     */
    public function displayCached($path, $input, callable $rendered, array $variables, &$success = null)
    {
        $__pug_parameters = $variables;
        $__pug_path = $this->cache($path, $input, $rendered, $success);

        $this->execute(function () use ($__pug_path, &$__pug_parameters) {
            extract($__pug_parameters);
            include $__pug_path;
        }, $__pug_parameters);
    }

    /**
     * Cache a template file in the cache directory (even if the cache is up to date).
     * Returns the number of bytes written in the cache file or false if a
     * failure occurred.
     *
     * @param string $path pug file
     *
     * @return bool|int
     */
    public function cacheFile($path)
    {
        $outputFile = $path;
        $this->isCacheUpToDate($outputFile);
        $compiler = $this->getRenderer()->getCompiler();

        return $this->cacheFileContents(
            $outputFile,
            $compiler->compileFile($path),
            $compiler->getCurrentImportPaths()
        );
    }

    /**
     * Cache a template file in the cache directory if the cache is obsolete.
     * Returns true if the cache is up to date and cache not change,
     * else returns the number of bytes written in the cache file or false if
     * a failure occurred.
     *
     * @param string $path
     *
     * @return bool|int
     */
    public function cacheFileIfChanged($path)
    {
        $outputFile = $path;
        if (!$this->isCacheUpToDate($outputFile)) {
            $compiler = $this->getRenderer()->getCompiler();

            return $this->cacheFileContents(
                $outputFile,
                $compiler->compileFile($path),
                $compiler->getCurrentImportPaths()
            );
        }

        return true;
    }

    /**
     * Scan a directory recursively for its views, compile them and save them into the cache directory.
     *
     * @param string[]|string $directory the directory to search pug files in it.
     *
     * @throws \Phug\RendererException
     *
     * @return array count of cached files and error count
     */
    public function cacheDirectory($directory)
    {
        $upToDateCheck = $this->getOption('up_to_date_check');
        $this->setOption('up_to_date_check', true);

        $renderer = $this->getRenderer();
        $tasks = new TasksGroup($renderer);
        $events = $renderer->getCompiler()->getEventListeners();
        $cacheDirectory = $this->getCacheDirectory();
        $cacheTrimLength = mb_strlen($cacheDirectory) + 1;
        $renderer->emptyDirectory($cacheDirectory);
        $directories = $this->parseCliDirectoriesInput($directory);

        foreach ($renderer->scanDirectories($directories) as $index => list($directory, $inputFile)) {
            $compiler = $this->reInitCompiler($renderer, $events);
            $path = $inputFile;
            $normalizedPath = $this->normalizePath($compiler, $path, $directory);
            $this->isCacheUpToDate($path);

            $tasks->runInSandBox(
                function () use ($index, $compiler, $path, $inputFile, $normalizedPath, $cacheTrimLength) {
                    return $this->compileAndCache($compiler, $path, $inputFile) &&
                        $this->registerCachedFile($index, $normalizedPath, mb_substr($path, $cacheTrimLength));
                },
                compact(['directory', 'inputFile', 'path'])
            );
        }

        $this->setOption('up_to_date_check', $upToDateCheck);

        return $tasks->getResult();
    }

    /**
     * Compile then render a file with given locals.
     *
     * @param string $__pug_php
     * @param array  $__pug_parameters
     */
    public function display($__pug_php, array $__pug_parameters)
    {
        $this->execute(function () use ($__pug_php, &$__pug_parameters) {
            extract($__pug_parameters);
            include ${'__pug_adapter'}->getCompiledFile($__pug_php);
        }, $__pug_parameters);
    }

    /**
     * Translates a given path by searching it in the passed locations and with the passed extensions.
     *
     * @param string $path       the file path to translate.
     * @param array  $locations  the directories to search in.
     * @param array  $extensions the file extensions to search for (e.g. ['.jd', '.pug'].
     *
     * @return string
     */
    public function locate($path, array $locations, array $extensions)
    {
        return $this->getRegistryPath($path, $extensions);
    }

    protected function registerCachedFile($directoryIndex, $source, $cacheFile)
    {
        $registryFile = $this->getCachePath('registry');
        $registry = file_exists($registryFile) ? include $registryFile : [];
        $base = &$registry;

        foreach ($this->getRegistryPathChunks($source, $directoryIndex) as $index => $path) {
            if (!isset($base[$path])) {
                $base[$path] = [];
            }

            $base = &$base[$path];
        }

        $base = $cacheFile;

        return file_put_contents($registryFile, '<?php return '.var_export($registry, true).';') > 0;
    }

    protected function createTemporaryFile()
    {
        return call_user_func(
            $this->getOption('tmp_name_function'),
            $this->getOption('tmp_dir'),
            'pug'
        );
    }

    protected function getCompiledFile($php)
    {
        $this->renderingFile = $this->createTemporaryFile();
        file_put_contents($this->renderingFile, $php);

        return $this->renderingFile;
    }

    /**
     * Return a file path in the cache for a given name (without extension added).
     *
     * @param string $file
     *
     * @return string
     */
    private function getRawCachePath($file)
    {
        $cacheDir = $this->getCacheDirectory();

        return str_replace('//', '/', $cacheDir.'/'.$file);
    }

    /**
     * Return a file path with extension added in the cache for a given name.
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
     * Get path from the cache registry (if up_to_date_check set to false only).
     *
     * @param string   $path       required view path.
     * @param string[] $extensions optional list of extensions to try.
     *
     * @return string|false false if no path registered, the path else.
     */
    private function getRegistryPath($path, array $extensions = [])
    {
        if ($this->getOption('up_to_date_check')) {
            return false;
        }

        if ($this->hasOption('extensions')) {
            $extensions = array_merge($extensions, $this->getOption('extensions'));
        }

        $cachePath = $this->findCachePathInRegistryFile($path, $this->getCachePath('registry'), $extensions);

        if ($cachePath) {
            return $this->getRawCachePath($cachePath);
        }

        return false;
    }

    private function checkPathExpiration(&$path)
    {
        $compiler = $this->getRenderer()->getCompiler();
        $input = $compiler->resolve($path);
        $path = $this->getCachePath(
            ($this->getOption('keep_base_name') ? basename($path) : '').
            $this->hashPrint($input)
        );

        // If up_to_date_check never refresh the cache
        if (!$this->getOption('up_to_date_check')) {
            return true;
        }

        // If there is no cache file, create it
        if (!file_exists($path)) {
            return false;
        }

        // Else check the main input path and all imported paths in the template
        return !$this->hasExpiredImport($input, $path);
    }

    /**
     * Return true if the file or content is up to date in the cache folder,
     * false else.
     *
     * @param &string $path  to be filled
     * @param string  $input file or pug code
     *
     * @return bool
     */
    private function isCacheUpToDate(&$path, $input = null)
    {
        if (!$input) {
            $registryPath = $this->getRegistryPath($path);

            if ($registryPath !== false) {
                $path = $registryPath;

                return true;
            }

            return $this->checkPathExpiration($path);
        }

        $path = $this->getCachePath($this->hashPrint($input));

        // Do not re-parse file if the same hash exists
        return file_exists($path);
    }

    private function getCacheDirectory()
    {
        $cacheFolder = $this->hasOption('cache_dir')
            ? $this->getOption('cache_dir')
            : null;
        if (!$cacheFolder && $cacheFolder !== false) {
            $cacheFolder = $this->getRenderer()->hasOption('cache_dir')
                ? $this->getRenderer()->getOption('cache_dir')
                : null;
        }
        if ($cacheFolder === true) {
            $cacheFolder = $this->getOption('tmp_dir');
        }

        if (!is_dir($cacheFolder) && !@mkdir($cacheFolder, 0777, true)) {
            throw new RuntimeException(
                $cacheFolder.': Cache directory doesn\'t exist.'."\n".
                'Create it with:'."\n".
                'mkdir -p '.escapeshellarg(realpath($cacheFolder))."\n".
                'Or replace your cache setting with a valid writable folder path.',
                5
            );
        }

        return $cacheFolder;
    }
}
