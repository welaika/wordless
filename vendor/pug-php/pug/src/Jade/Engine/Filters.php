<?php

namespace Jade\Engine;

use Jade\Compiler\FilterHelper;

/**
 * Class Jade\Engine\Filters.
 */
abstract class Filters extends Extensions
{
    /**
     * Register / override new filter.
     *
     * @param string name
     * @param callable filter
     *
     * @return $this
     */
    public function filter($name, $filter)
    {
        $this->filters[$name] = $filter;

        return $this;
    }

    /**
     * Check if a filter is registered.
     *
     * @param string name
     *
     * @return bool
     */
    public function hasFilter($name)
    {
        $helper = new FilterHelper($this->filters, $this->options['filterAutoLoad']);

        return $helper->hasFilter($name);
    }

    /**
     * Get a registered filter by name.
     *
     * @param string name
     *
     * @return callable
     */
    public function getFilter($name)
    {
        $helper = new FilterHelper($this->filters, $this->options['filterAutoLoad']);

        return $helper->getFilter($name);
    }
}
