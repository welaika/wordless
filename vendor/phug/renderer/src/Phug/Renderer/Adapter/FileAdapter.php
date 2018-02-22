<?php

namespace Phug\Renderer\Adapter;

use Phug\Renderer;
use Phug\Renderer\AbstractAdapter;
use Phug\Renderer\CacheInterface;
use RuntimeException;

class FileAdapter extends AbstractAdapter implements CacheInterface
{
    private $renderingFile;

    public function __construct(Renderer $renderer, $options)
    {
        parent::__construct($renderer, [
            'cache_dir'           => null,
            'tmp_dir'             => sys_get_temp_dir(),
            'tmp_name_function'   => 'tempnam',
            'up_to_date_check'    => true,
            'keep_base_name'      => false,
        ]);

        $this->setOptions($options);
    }

    protected function cacheFileContents($destination, $output, $importsMap = [])
    {
        $imports = file_put_contents(
            $destination.'.imports.serialize.txt',
            serialize($importsMap)
        ) ?: 0;
        $template = file_put_contents($destination, $output);

        return $template === false ? false : $template + $imports;
    }

    /**
     * Return the cached file path after cache optional process.
     *
     * @param $path
     * @param string   $input    pug input
     * @param callable $rendered method to compile the source into PHP
     * @param bool     $success
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
     * @param $path
     * @param string   $input     pug input
     * @param callable $rendered  method to compile the source into PHP
     * @param array    $variables local variables
     * @param bool     $success
     */
    public function displayCached($path, $input, callable $rendered, array $variables, &$success = null)
    {
        $__pug_parameters = $variables;
        $__pug_path = $this->cache($path, $input, $rendered, $success);

        call_user_func(function () use ($__pug_path, $__pug_parameters) {
            extract($__pug_parameters);
            include $__pug_path;
        });
    }

    /**
     * Cache a template file in the cache directory (even if the cache is up to date).
     * Returns the number of bytes written in the cache file or false if a
     * failure occurred.
     *
     * @param string $path
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
     * Scan a directory recursively, compile them and save them into the cache directory.
     *
     * @param string $directory the directory to search in pug templates
     *
     * @return array count of cached files and error count
     */
    public function cacheDirectory($directory)
    {
        $success = 0;
        $errors = 0;
        $errorDetails = [];

        $renderer = $this->getRenderer();
        $compiler = $renderer->getCompiler();

        foreach ($renderer->scanDirectory($directory) as $inputFile) {
            $path = $inputFile;
            $this->isCacheUpToDate($path);
            $sandBox = $this->getRenderer()->getNewSandBox(function () use (&$success, $compiler, $path, $inputFile) {
                $this->cacheFileContents($path, $compiler->compileFile($inputFile), $compiler->getCurrentImportPaths());
                $success++;
            });

            if ($sandBox->getThrowable()) {
                $errors++;
                $errorDetails[] = [
                    'directory' => $directory,
                    'inputFile' => $inputFile,
                    'path'      => $path,
                    'error'     => $sandBox->getThrowable(),
                ];
            }
        }

        return [$success, $errors, $errorDetails];
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

    public function display($__pug_php, array $__pug_parameters)
    {
        extract($__pug_parameters);
        include $this->getCompiledFile($__pug_php);
    }

    public function getRenderingFile()
    {
        return $this->renderingFile;
    }

    /**
     * Return a file path in the cache for a given name.
     *
     * @param string $name
     *
     * @return string
     */
    private function getCachePath($name)
    {
        $cacheDir = $this->getCacheDirectory();

        return str_replace('//', '/', $cacheDir.'/'.$name).'.php';
    }

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
