<?php

namespace Phug\Renderer\Partial;

/**
 * Trait FileSystemTrait: require OptionInterface to be implemented.
 */
trait FileSystemTrait
{
    /**
     * Get all file matching extensions list recursively in a directory.
     *
     * @param string   $directory         directory to scan for files
     * @param array    $extensions        optional extensions to filter the result (use 'extensions' setting if omitted)
     * @param callable $directoryCallback optional function/closure to call for each sub-directory scanned
     *                                    (after files have been yielded).
     * @param callable $fileCallback      optional function/closure to call after each file yielded.
     *
     * @return iterable
     */
    public function scanDirectory($directory, $extensions = null, $directoryCallback = null, $fileCallback = null)
    {
        if ($extensions === null) {
            $extensions = $this->getOption('extensions');
        }

        foreach (scandir($directory) as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }

            $inputFile = $directory.DIRECTORY_SEPARATOR.$object;

            if (is_dir($inputFile)) {
                foreach ($this->scanDirectory($inputFile, $extensions, $directoryCallback, $fileCallback) as $file) {
                    yield $file;
                }

                if ($directoryCallback) {
                    call_user_func($directoryCallback, $inputFile);
                }

                continue;
            }

            if ($extensions === false || $this->fileMatchExtensions($object, $extensions)) {
                yield $inputFile;

                if ($fileCallback) {
                    call_user_func($fileCallback, $inputFile);
                }
            }
        }
    }

    /**
     * Get all file matching extensions list recursively in a directories list.
     *
     * @param $directories
     *
     * @return iterable
     */
    public function scanDirectories(array $directories)
    {
        foreach (array_filter($directories, 'is_dir') as $directory) {
            foreach ($this->scanDirectory($directory) as $file) {
                yield [$directory, $file];
            }
        }
    }

    /**
     * Remove all files and directories from a given directory.
     *
     * @param string $directory directory to empty.
     */
    public function emptyDirectory($directory)
    {
        if (is_dir($directory)) {
            iterator_count($this->scanDirectory($directory, false, 'rmdir', 'unlink'));
        }
    }

    /**
     * Returns true if the given path has one of the given extensions.
     *
     * @param string $path       file path
     * @param array  $extensions extensions list
     *
     * @return bool
     */
    protected function fileMatchExtensions($path, $extensions)
    {
        foreach ($extensions as $extension) {
            if (mb_substr($path, -mb_strlen($extension)) === $extension) {
                return true;
            }
        }

        return false;
    }
}
