<?php

namespace Phug\Compiler\Locator;

use Phug\Compiler\LocatorInterface;

class FileLocator implements LocatorInterface
{
    private function normalize($path)
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    private function getFullPath($location, $path, $extension)
    {
        $fullPath = "$location/$path$extension";

        if (@is_file($fullPath) && is_readable($fullPath)) {
            return realpath($fullPath);
        }

        $length = strlen($extension);

        if ($length && substr($path, -$length) === $extension &&
            @is_file($fullPath = "$location/$path") && is_readable($fullPath)
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
