<?php

namespace Pug\Engine;

use InvalidArgumentException;
use Phug\Renderer;

/**
 * Class Pug\Engine\Filters.
 */
abstract class Filters extends Renderer
{
    /**
     * Register / override new filter.
     *
     * @param string   $name
     * @param callable $filter
     *
     * @return $this
     */
    public function setFilter($name, $filter)
    {
        if (!(
            is_callable($filter) ||
            class_exists($filter) ||
            method_exists($filter, 'parse')
        )) {
            throw new InvalidArgumentException(
                'Invalid ' . $name . ' filter given: ' .
                'it must be a callable or a class name.'
            );
        }

        return $this->getCompiler()->setFilter($name, $filter);
    }

    /**
     * @alias setFilter
     */
    public function filter($name, $filter)
    {
        return $this->setFilter($name, $filter);
    }

    /**
     * Check if a filter is registered.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasFilter($name)
    {
        return $this->getCompiler()->hasFilter($name);
    }

    /**
     * Get a registered / resolvable filter by name.
     *
     * @param string $name
     *
     * @return callable
     */
    public function getFilter($name)
    {
        $filter = $this->getCompiler()->getFilter($name);
        if (is_string($filter) && !function_exists($filter) && class_exists($filter)) {
            $filter = new $filter();
        }

        return $filter;
    }
}
