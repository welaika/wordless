<?php

namespace Jade\Engine;

use Jade\Parser\ExtensionsHelper;

/**
 * Class Jade\Engine\Extensions.
 */
abstract class Extensions
{
    /**
     * Get main template file extension.
     *
     * @return string
     */
    public function getExtension()
    {
        $extensions = new ExtensionsHelper($this->getOption('extension'));

        return $extensions->getFirst();
    }

    /**
     * Get list of supported extensions.
     *
     * @return array
     */
    public function getExtensions()
    {
        $extensions = new ExtensionsHelper($this->getOption('extension'));

        return $extensions->getExtensions();
    }
}
