<?php

namespace Jade\Engine;

use Jade\Compiler\CacheHelper;

/**
 * Class Jade\Engine\Cache.
 */
abstract class Cache extends Filters
{
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
        $cache = new CacheHelper($this);

        return $cache->cache($input);
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
        $cache = new CacheHelper($this);

        return $cache->cacheDirectory($directory);
    }
}
