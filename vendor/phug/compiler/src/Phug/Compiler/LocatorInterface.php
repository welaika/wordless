<?php

namespace Phug\Compiler;

/**
 * Interface LocatorInterface.
 *
 * An interface for locators that translate given file paths to full fill paths.
 */
interface LocatorInterface
{
    /**
     * Translates a given path by searching it in the passed locations and with the passed extensions.
     *
     * @param string $path       the file path to translate.
     * @param array  $locations  the directories to search in.
     * @param array  $extensions the file extensions to search for (e.g. ['.jd', '.pug'].
     *
     * @return string
     */
    public function locate($path, array $locations, array $extensions);
}
