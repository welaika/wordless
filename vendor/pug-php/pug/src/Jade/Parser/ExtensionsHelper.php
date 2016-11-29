<?php

namespace Jade\Parser;

class ExtensionsHelper
{
    protected $extensions;

    public function __construct($extensions)
    {
        $this->extensions = is_string($extensions)
            ? array($extensions)
            : array_unique($extensions);
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    public function getFirst($defaultValue = '')
    {
        return isset($this->extensions[0])
            ? $this->extensions[0]
            : $defaultValue;
    }

    public function hasValidTemplateExtension($path)
    {
        foreach ($this->getExtensions() as $extension) {
            if (substr($path, -strlen($extension)) === $extension) {
                return true;
            }
        }

        return false;
    }

    public function findValidTemplatePath($path)
    {
        $extensions = $this->getExtensions();
        foreach (array_slice(func_get_args(), 1) as $extension) {
            $extensions[] = $extension;
        }
        foreach ($extensions as $extension) {
            if (file_exists($path . $extension)) {
                return realpath($path . $extension);
            }
        }
    }
}
