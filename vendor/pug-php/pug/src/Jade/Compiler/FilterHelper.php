<?php

namespace Jade\Compiler;

/**
 * Class Jade CompilerUtils.
 * Internal static methods of the compiler.
 */
class FilterHelper
{
    protected $filters = array();
    protected $filterAutoLoad = true;

    public function __construct(array $filters, $filterAutoLoad)
    {
        $this->filters = $filters;
        $this->filterAutoLoad = $filterAutoLoad;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasFilter($name)
    {
        return !is_null($this->getFilter($name));
    }

    /**
     * @param $name
     *
     * @return callable
     */
    public function getFilter($name)
    {
        // Check that filter is registered
        if (array_key_exists($name, $this->filters)) {
            return $this->filters[$name];
        }

        if (!$this->filterAutoLoad) {
            return;
        }

        // Else check if a class with a name that match can be loaded
        foreach (array('Pug', 'Jade') as $namespace) {
            $filter = $namespace . '\\Filter\\' . implode('', array_map('ucfirst', explode('-', $name)));
            if (class_exists($filter)) {
                return $filter;
            }
        }
    }

    /**
     * @param $name
     *
     * @return callable
     */
    public function getValidFilter($name)
    {
        if ($filter = $this->getFilter($name)) {
            return $filter;
        }

        throw new \InvalidArgumentException($name . ': Filter doesn\'t exists', 17);
    }
}
