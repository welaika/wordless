<?php

namespace Phug\Compiler;

/**
 * Interface WithUpperLocatorInterface.
 *
 * An interface for object than can have an upper locator.
 * Used to get the precedence over the file locator, such
 * as the cache registry locator.
 */
interface WithUpperLocatorInterface
{
    /**
     * Set a master locator to use before the internal one.
     *
     * @param LocatorInterface|null $upperLocator locator strategy
     */
    public function setUpperLocator($upperLocator);
}
