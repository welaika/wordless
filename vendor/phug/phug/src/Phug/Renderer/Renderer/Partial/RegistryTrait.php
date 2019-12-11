<?php

namespace Phug\Renderer\Partial;

trait RegistryTrait
{
    /**
     * Yield list of path chunks for registry for a given path.
     *
     * @param string   $source
     * @param int|null $directoryIndex
     *
     * @return iterable
     */
    protected function getRegistryPathChunks($source, $directoryIndex = null)
    {
        $paths = explode('/', $source);
        $lastIndex = count($paths) - 1;

        foreach ($paths as $index => $path) {
            yield ($index < $lastIndex ? 'd:' : 'f:').$path;
        }

        if ($directoryIndex !== null) {
            yield 'i:'.$directoryIndex;
        }
    }

    /**
     * Return the first value indexed with "i:" prefix as raw cash path.
     *
     * @param array|null $registry registry result
     *
     * @return string|false
     */
    protected function getFirstRegistryIndex($registry)
    {
        foreach (((array) $registry) as $index => $value) {
            if (substr($index, 0, 2) === 'i:') {
                return $value;
            }
        }

        return false;
    }

    /**
     * Find the path of a cached file for a given path in a given registry.
     *
     * @param string   $path       path to find in the registry
     * @param array    $registry   registry data array
     * @param string[] $extensions extensions to try to add to the file path if not found
     *
     * @return string|false
     */
    protected function findCachePathInRegistry($path, $registry, $extensions = [])
    {
        $entry = $this->findInRegistry($path, $registry, $extensions);

        if ($entry === false || is_string($entry)) {
            return $entry;
        }

        return $this->getFirstRegistryIndex($entry);
    }

    /**
     * Find the path of a cached file for a given path in a given registry file (that may not exist).
     *
     * @param string   $path         path to find in the registry
     * @param string   $registryFile registry file path
     * @param string[] $extensions   extensions to try to add to the file path if not found
     *
     * @return string|false
     */
    protected function findCachePathInRegistryFile($path, $registryFile, $extensions = [])
    {
        if (!file_exists($registryFile)) {
            return false;
        }

        return $this->findCachePathInRegistry($path, include $registryFile, $extensions);
    }

    /**
     * Try to append extension to find a key in a given array if it's
     * file registry key.
     *
     * @param array    $registry
     * @param string   $key
     * @param string[] $extensions
     *
     * @return bool|mixed
     */
    private function tryExtensionsOnFileKey($registry, $key, $extensions)
    {
        return substr($key, 0, 2) === 'f:'
            ? $this->tryExtensions($registry, $key, $extensions)
            : false;
    }

    /**
     * Try to append extension to find a key in a given array assuming it's
     * file registry key.
     *
     * @param array    $registry
     * @param string   $key
     * @param string[] $extensions
     *
     * @return bool|mixed
     */
    private function tryExtensions($registry, $key, $extensions)
    {
        foreach ($extensions as $extension) {
            if (isset($registry[$key.$extension])) {
                return $registry[$key.$extension];
            }
        }

        return false;
    }

    /**
     * Find raw entry of a cached file for a given path in a given registry.
     *
     * @param string   $path       path to find in the registry
     * @param array    $registry   registry data array
     * @param string[] $extensions extensions to try to add to the file path if not found
     *
     * @return string|array|false
     */
    private function findInRegistry($path, $registry, $extensions)
    {
        foreach ($this->getRegistryPathChunks($path) as $key) {
            if (!isset($registry[$key])) {
                return $this->tryExtensionsOnFileKey($registry, $key, $extensions);
            }

            $registry = $registry[$key];
        }

        return $registry;
    }
}
