<?php

namespace Phug\Partial;

trait CallbacksTrait
{
    /**
     * @var callable[][]
     */
    private $callbacks;

    /**
     * Add a callback in the group of the given method name.
     *
     * @param string   $methodName
     * @param callable $callback
     */
    protected function addCallback($methodName, callable $callback)
    {
        if (!isset($this->callbacks[$methodName])) {
            $this->callbacks[$methodName] = [];
        }

        $this->callbacks[$methodName][] = $callback;
    }

    /**
     * Get callbacks for a given method name.
     *
     * @param string $methodName if you pass a name including the class (such as MyClass::myMethod),
     *                           then class name is ignored.
     *
     * @return callable[]
     */
    protected function getCallbacks($methodName)
    {
        $chunks = explode('::', $methodName);
        $group = end($chunks);

        return isset($this->callbacks[$group]) ? $this->callbacks[$group] : [];
    }
}
