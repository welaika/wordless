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
     * Returns current event listeners by event name.
     *
     * @return ListenerQueue[]
     */
    public function getEventListeners()
    {
        return $this->eventListeners;
    }

    /**
     * Merge current events listeners with a given list.
     *
     * @param ListenerQueue[]|EventManagerInterface $eventListeners event listeners by event name
     *
     * @return bool true on success false on failure
     */
    public function mergeEventListeners($eventListeners)
    {
        if ($eventListeners instanceof EventManagerInterface) {
            $eventListeners = $eventListeners->getEventListeners();
        }

        foreach (((array) $eventListeners) as $eventName => $listeners) {
            $queue = [];

            if (isset($this->eventListeners[$eventName])) {
                $innerListeners = clone $this->eventListeners[$eventName];
                $innerListeners->setExtractFlags(ListenerQueue::EXTR_DATA);

                foreach ($innerListeners as $callback) {
                    $queue[] = $callback;
                }
            }

            $listeners = clone $listeners;
            $listeners->setExtractFlags(ListenerQueue::EXTR_BOTH);

            foreach ($listeners as $listener) {
                if (!in_array($listener['data'], $queue)) {
                    $this->attach($eventName, $listener['data'], $listener['priority']);
                }
            }
        }

        return true;
    }

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
        $listeners->setExtractFlags(ListenerQueue::EXTR_DATA);
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
