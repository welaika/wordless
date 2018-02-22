<?php

namespace Phug\Compiler\Locator;

use Phug\Compiler\LocatorInterface;

class FileLocator implements LocatorInterface
{
    private function normalize($path)
    {
        return rtrim(str_replace('\\', '/', $path), '/');
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
                $fullPath = "$location/$path$extension";

                if (@is_file($fullPath) && is_readable($fullPath)) {
                    return realpath($fullPath);
                }
            }
        }

        return null;
    }
}
