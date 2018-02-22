<?php

namespace Phug\Renderer\Partial;

trait CacheTrait
{
    use AdapterTrait;

    /**
     * @return \Phug\Renderer\CacheInterface
     */
    private function getCacheAdapter()
    {
        $this->expectCacheAdapter();

        return $this->getAdapter();
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
        return $this->getCacheAdapter()->cacheFile($path);
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
        return $this->getCacheAdapter()->cacheFileIfChanged($path);
    }

    /**
     * Cache all templates in a directory in the cache directory you specified with the cache_dir option.
     * You should call after deploying your application in production to avoid a slower page loading for the first
     * user.
     *
     * @param $directory
     *
     * @return array
     */
    public function cacheDirectory($directory)
    {
        return $this->getCacheAdapter()->cacheDirectory($directory);
    }
}
