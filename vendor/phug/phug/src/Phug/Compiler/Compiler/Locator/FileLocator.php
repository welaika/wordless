<?php

namespace Phug\Compiler\Locator;

use Phug\Compiler\LocatorInterface;
use Phug\Compiler\NormalizerInterface;

class FileLocator implements LocatorInterface, NormalizerInterface
{
    public function normalize($path)
    {
        $path = implode('/', func_get_args());
        $paths = explode('/', rtrim(preg_replace('`[\\\\/]+`', '/', $path), '/'));
        $chunks = [];

        foreach ($this->getConsistentPaths($paths) as $path) {
            if ($path === '..' && ($count = count($chunks)) && $chunks[$count - 1] !== '..') {
                array_pop($chunks);

                continue;
            }

            $chunks[] = $path;
        }

        return implode('/', $chunks);
    }

    private function getConsistentPaths($paths)
    {
        foreach ($paths as $path) {
            if ($path === '.') {
                continue;
            }

            yield $path;
        }
    }

    private function getFullPath($location, $path, $extension)
    {
        $fullPath = $this->normalize($location, $path.$extension);

        if (@is_file($fullPath) && is_readable($fullPath)) {
            return realpath($fullPath);
        }

        $length = strlen($extension);

        if ($length && substr($path, -$length) === $extension &&
            @is_file($fullPath = $this->normalize($location, $path)) && is_readable($fullPath)
        ) {
            return realpath($fullPath);
        }

        return null;
    }

    public function locate($path, array $locations, array $extensions)
    {
        // @ catch softly PHP open_basedir restriction
        if (@is_file($path)) {
            return is_readable($path) ? realpath($path) : null;
        }

        $path = ltrim($this->normalize($path), '/');
        $locations = array_reverse($locations);

        foreach ($locations as $location) {
            $location = $this->normalize($location);

            foreach ($extensions as $extension) {
                if ($fullPath = $this->getFullPath($location, $path, $extension)) {
                    return $fullPath;
                }
            }
        }

        return null;
    }
}
