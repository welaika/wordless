<?php

namespace Phug;

class Event implements EventInterface
{
    private $name;
    private $target;
    private $params;
    private $propagationStopped = false;

    /**
     * Event constructor.
     *
     * @param $name
     * @param $target
     * @param $params
     */
    public function __construct($name, $target = null, $params = [])
    {
        $this->name = $name;
        $this->target = $target;
        $this->params = $params;
    }

    /**
     * Get event name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get target/context from which event was triggered.
     *
     * @return null|string|object
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Get parameters passed to the event.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get a single parameter by name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParam($name)
    {
        return $this->params[$name];
    }

    /**
     * Set the event name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Set the event target.
     *
     * @param null|string|object $target
     *
     * @return void
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Set event parameters.
     *
     * @param array $params
     *
     * @return void
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * Indicate whether or not to stop propagating this event.
     *
     * @param bool $flag
     */
    public function stopPropagation($flag)
    {
        $this->propagationStopped = $flag;
    }

    /**
     * Has this event indicated event propagation should stop?
     *
     * @return bool
     */
    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }
}
