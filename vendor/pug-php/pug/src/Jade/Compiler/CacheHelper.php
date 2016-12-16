<?php

namespace Jade\Compiler;

use Jade\Jade;
use Jade\Parser\ExtensionsHelper;

class CacheHelper
{
    protected $pug;

    public function __construct(Jade $pug)
    {
        $this->pug = $pug;
    }

    /**
     * Return a file path in the cache for a given name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getCachePath($name)
    {
        return str_replace('//', '/', $this->pug->getOption('cache') . '/' . $name) . '.php';
    }

    /**
     * Return a hashed print from input file or content.
     *
     * @param string $input
     *
     * @return string
     */
    protected function hashPrint($input)
    {
        // Get the stronger hashing algorithm available to minimize collision risks
        $algos = hash_algos();
        $algo = $algos[0];
        $number = 0;
        foreach ($algos as $hashAlgorithm) {
            if (strpos($hashAlgorithm, 'md') === 0) {
                $hashNumber = substr($hashAlgorithm, 2);
                if ($hashNumber > $number) {
                    $number = $hashNumber;
                    $algo = $hashAlgorithm;
                }
                continue;
            }
            if (strpos($hashAlgorithm, 'sha') === 0) {
                $hashNumber = substr($hashAlgorithm, 3);
                if ($hashNumber > $number) {
                    $number = $hashNumber;
                    $algo = $hashAlgorithm;
                }
                continue;
            }
        }

        return rtrim(strtr(base64_encode(hash($algo, $input, true)), '+/', '-_'), '=');
    }

    /**
     * Return true if the file or content is up to date in the cache folder,
     * false else.
     *
     * @param string  $input file or pug code
     * @param &string $path  to be filled
     *
     * @return bool
     */
    protected function isCacheUpToDate($input, &$path)
    {
        if (is_file($input)) {
            $path = $this->getCachePath(
                ($this->pug->getOption('keepBaseName') ? basename($input) : '') .
                $this->hashPrint(realpath($input))
            );

            // Do not re-parse file if original is older
            return (!$this->pug->getOption('upToDateCheck')) || (file_exists($path) && filemtime($input) < filemtime($path));
        }

        $path = $this->getCachePath($this->hashPrint($input));

        // Do not re-parse file if the same hash exists
        return file_exists($path);
    }

    protected function getCacheDirectory()
    {
        $cacheFolder = $this->pug->getOption('cache');

        if (!is_dir($cacheFolder)) {
            throw new \ErrorException($cacheFolder . ': Cache directory seem\'s to not exists', 5);
        }

        return $cacheFolder;
    }

    /**
     * Get cached input/file a matching cache file exists.
     * Else, render the input, cache it in a file and return it.
     *
     * @param string input
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return string
     */
    public function cache($input)
    {
        $cacheFolder = $this->getCacheDirectory();

        if ($this->isCacheUpToDate($input, $path)) {
            return $path;
        }

        if (!is_writable($cacheFolder)) {
            throw new \ErrorException(sprintf('Cache directory must be writable. "%s" is not.', $cacheFolder), 6);
        }

        $rendered = $this->pug->compile($input);
        file_put_contents($path, $rendered);

        return $this->pug->stream($rendered);
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

        $extensions = new ExtensionsHelper($this->pug->getOption('extension'));

        foreach (scandir($directory) as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }
            $input = $directory . DIRECTORY_SEPARATOR . $object;
            if (is_dir($input)) {
                list($subSuccess, $subErrors) = $this->cacheDirectory($input);
                $success += $subSuccess;
                $errors += $subErrors;
                continue;
            }
            if ($extensions->hasValidTemplateExtension($object)) {
                $this->isCacheUpToDate($input, $path);
                try {
                    file_put_contents($path, $this->pug->compile($input));
                    $success++;
                } catch (\Exception $e) {
                    $errors++;
                }
            }
        }

        return array($success, $errors);
    }
}
