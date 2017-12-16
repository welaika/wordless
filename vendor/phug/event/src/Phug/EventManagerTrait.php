<?php

namespace Phug;

use Phug\Event\ListenerQueue;

trait EventManagerTrait
{
    /**
     * @var ListenerQueue[]
     */
    private $eventListeners = [];

    /**
     * Attaches a listener to an event.
     *
     * @param string   $event    the event to attach too
     * @param callable $callback a callable function
     * @param int      $priority the priority at which the $callback executed
     *
     * @return bool true on success false on failure
     */
    public function attach($event, $callback, $priority = 0)
    {
        if (!isset($this->eventListeners[$event])) {
            $this->clearListeners($event);
        }

        $this->eventListeners[$event]->insert($callback, $priority);

        return true;
    }

    /**
     * Detaches a listener from an event.
     *
     * @param string   $event    the event to attach too
     * @param callable $callback a callable function
     *
     * @return bool true on success false on failure
     */
    public function detach($event, $callback)
    {
        if (!isset($this->eventListeners[$event]) || $this->eventListeners[$event]->isEmpty()) {
            return false;
        }

        $removed = false;
        $listeners = $this->eventListeners[$event];
        $newListeners = new ListenerQueue();

        $listeners->setExtractFlags(ListenerQueue::EXTR_BOTH);
        foreach ($listeners as $item) {
            if ($item['data'] === $callback) {
                $removed = true;
                continue;
            }

            $newListeners->insert($item['data'], $item['priority']);
        }
        $listeners->setExtractFlags(ListenerQueue::EXTR_DATA);

        $this->eventListeners[$event] = $newListeners;

        return $removed;
    }

    /**
     * Clear all listeners for a given event.
     *
     * @param string $event
     *
     * @return void
     */
    public function clearListeners($event)
    {
        $this->eventListeners[$event] = new ListenerQueue();
    }

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
    public function trigger($event, $target = null, $argv = [])
    {
        $event = $event instanceof EventInterface
            ? $event
            : new Event($event);

        $event->setTarget($target ?: $this);
        $event->setParams($argv);

        $eventName = $event->getName();

        if (!isset($this->eventListeners[$eventName]) || $this->eventListeners[$eventName]->isEmpty()) {
            return null;
        }

        $listeners = clone $this->eventListeners[$eventName];
        $result = null;
        foreach ($listeners as $callback) {
            $result = call_user_func($callback, $event);

            if ($event->isPropagationStopped()) {
                return $result;
            }
        }

        return $result;
    }
}
