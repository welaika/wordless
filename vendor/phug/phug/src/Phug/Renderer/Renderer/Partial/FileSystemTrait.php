<?php

namespace Phug\Renderer\Partial;

/**
 * Trait FileSystemTrait: require OptionInterface to be implemented.
 */
trait FileSystemTrait
{
    protected function fileMatchExtensions($path, $extensions)
    {
        foreach ($extensions as $extension) {
            if (mb_substr($path, -mb_strlen($extension)) === $extension) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all file matching extensions list recursively in a directory.
     *
     * @param $directory
     *
     * @return \Generator
     */
    public function scanDirectory($directory)
    {
        $extensions = $this->getOption('extensions');

        foreach (scandir($directory) as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }
            $inputFile = $directory.DIRECTORY_SEPARATOR.$object;
            if (is_dir($inputFile)) {
                foreach ($this->scanDirectory($inputFile) as $file) {
                    yield $file;
                }

                continue;
            }
            if ($this->fileMatchExtensions($object, $extensions)) {
                yield $inputFile;
            }
        }
    }
}
