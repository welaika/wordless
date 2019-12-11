<?php

namespace Phug\Renderer;

/**
 * CacheInterface describes additional methods an adapter must implement to be compatible with caching methods
 * and commands.
 */
interface CacheInterface
{
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
    public function cache($path, $input, callable $rendered, &$success = null);

    /**
     * Cache a template file in the cache directory (even if the cache is up to date).
     *
     * @param string $path
     *
     * @return bool
     */
    public function cacheFile($path);

    /**
     * Cache a template file in the cache directory if the cache is obsolete.
     *
     * @param string $path
     *
     * @return bool
     */
    public function cacheFileIfChanged($path);

    /**
     * @param string   $path      path to pug file
     * @param string   $input     pug input
     * @param callable $rendered  method to compile the source into PHP
     * @param array    $variables local variables
     * @param bool     $success
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function displayCached($path, $input, callable $rendered, array $variables, &$success = null);

    /**
     * @param string[]|string $directory the directory(ies) to search in pug templates
     *
     * @return array count of cached files and error count
     */
    public function cacheDirectory($directory);
}
