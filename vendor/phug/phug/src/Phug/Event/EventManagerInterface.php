<?php

namespace Phug;

use Phug\Event\ListenerQueue;

/**
 * Interface for EventManager.
 */
interface EventManagerInterface
{
    /**
     * Returns current event listeners by event name.
     *
     * @return ListenerQueue[]
     */
    public function getEventListeners();

    /**
     * Merge current events listeners with a given list.
     *
     * @param ListenerQueue[] $eventListeners event listeners by event name
     *
     * @return bool true on success false on failure
     */
    public function mergeEventListeners($eventListeners);

    /**
     * Attaches a listener to an event.
     *
     * @param string   $event    the event to attach too
     * @param callable $callback a callable function
     * @param int      $priority the priority at which the $callback executed
     *
     * @return bool true on success false on failure
     */
    public function attach($event, $callback, $priority = 0);

    /**
     * Detaches a listener from an event.
     *
     * @param string   $event    the event to attach too
     * @param callable $callback a callable function
     *
     * @return bool true on success false on failure
     */
    public function detach($event, $callback);

    /**
     * Clear all listeners for a given event.
     *
     * @param string $event
     *
     * @return void
     */
    public function clearListeners($event);

    /**
     * Trigger an event.
     *
     * Can accept an EventInterface or will create one if not passed
     *
     * @param string|EventInterface $event
     * @param object|string         $target
     * @param array|object          $argv
     *
     * @return mixed
     */
    public function trigger($event, $target = null, $argv = []);
}
