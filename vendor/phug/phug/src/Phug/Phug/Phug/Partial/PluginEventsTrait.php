<?php

namespace Phug\Partial;

use ReflectionException;

trait PluginEventsTrait
{
    /**
     * Get events lists to be sorted.
     *
     * @return array
     */
    abstract public function getEventsList();

    /**
     * Attaches a listener to an event.
     *
     * @param string   $event    the event to attach too
     * @param callable $callback a callable function
     * @param int      $priority the priority at which the $callback executed
     *
     * @throws ReflectionException
     *
     * @return bool true on success false on failure
     */
    abstract public function attachEvent($event, $callback, $priority = 0);

    /**
     * Detaches a listener from an event.
     *
     * @param string   $event    the event to attach too
     * @param callable $callback a callable function
     *
     * @throws ReflectionException
     *
     * @return bool true on success false on failure
     */
    abstract public function detachEvent($event, $callback);

    /**
     * @throws ReflectionException
     */
    public function attachEvents()
    {
        foreach ($this->getEventsList() as list($event, $listener)) {
            $this->attachEvent($event, $listener);
        }
    }

    /**
     * @throws ReflectionException
     */
    public function detachEvents()
    {
        foreach ($this->getEventsList() as list($event, $listener)) {
            $this->detachEvent($event, $listener);
        }
    }
}
