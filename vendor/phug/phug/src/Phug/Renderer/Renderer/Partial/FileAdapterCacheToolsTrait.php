<?php

namespace Phug\Renderer\Partial;

use Phug\CompilerInterface;
use Phug\Renderer;

trait FileAdapterCacheToolsTrait
{
    /**
     * Write the cache file with the given contents and map of imports.
     *
     * @param string $destination
     * @param string $output
     * @param array  $importsMap
     *
     * @return bool
     */
    protected function cacheFileContents($destination, $output, $importsMap = [])
    {
        $imports = file_put_contents(
            $destination.'.imports.serialize.txt',
            serialize($importsMap)
        ) ?: 0;
        $template = file_put_contents($destination, $output);

        return $template && $imports;
    }

    /**
     * @param Renderer $renderer
     * @param array    $events
     *
     * @throws \Phug\RendererException
     *
     * @return \Phug\CompilerInterface
     */
    protected function reInitCompiler(Renderer $renderer, array $events)
    {
        $renderer->initCompiler();
        $compiler = $renderer->getCompiler();
        $compiler->mergeEventListeners($events);

        return $compiler;
    }

    /**
     * Return directories list from a directory string as it is allowed in CLI:
     * - directory1/x/y
     * - [directory1/x/y,directory2/z]
     * as an array of strings.
     *
     * @param string $directory
     *
     * @return string[]
     */
    protected function parseCliDirectoriesInput($directory)
    {
        return array_filter(
            preg_match('/^\[(.*)]$/', $directory, $match)
                ? explode(',', $match[1])
                : [$directory],
            'strlen'
        );
    }

    /**
     * Compile a file with a given compiler and cache it.
     *
     * @param CompilerInterface $compiler
     * @param string            $path
     * @param string            $inputFile
     *
     * @return bool
     */
    protected function compileAndCache(CompilerInterface $compiler, $path, $inputFile)
    {
        return $this->cacheFileContents($path, $compiler->compileFile($inputFile), $compiler->getCurrentImportPaths());
    }

    /**
     * Compile a file with a given compiler and cache it.
     *
     * @param CompilerInterface $compiler
     * @param string            $path
     * @param string            $directory
     *
     * @return string
     */
    protected function normalizePath(CompilerInterface $compiler, $path, $directory)
    {
        $path = substr($path, strlen($directory) + 1);

        return method_exists($compiler, 'normalizePath') ? $compiler->normalizePath($path) : $path;
    }
}
