<?php

namespace Phug;

abstract class AbstractExtension implements ExtensionInterface
{
    /**
     * @return array
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getEvents()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getIncludes()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getScanners()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getKeywords()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getTokenHandlers()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getElementHandlers()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getPhpTokenHandlers()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getCompilers()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getFormats()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAssignmentHandlers()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getPatterns()
    {
        return [];
    }
}
