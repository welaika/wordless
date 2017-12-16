<?php

namespace Phug\Renderer;

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
     * @param string $directory the directory to search in pug templates
     *
     * @return array count of cached files and error count
     */
    public function cacheDirectory($directory);
}
