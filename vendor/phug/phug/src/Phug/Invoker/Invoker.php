<?php

namespace Phug;

use Phug\Event\ListenerQueue;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

class Invoker implements InvokerInterface
{
    /**
     * List of callbacks grouped by type.
     *
     * @var ListenerQueue[]
     */
    private $invokables;

    /**
     * Event constructor.
     *
     * @param callable[] $invokables list of callbacks to start with
     *
     * @throws ReflectionException
     */
    public function __construct(array $invokables = [])
    {
        $this->reset();
        $this->add($invokables);
    }

    /**
     * Remove all callbacks from the list.
     */
    public function reset()
    {
        $this->invokables = [];
    }

    /**
     * Get all callbacks from the list.
     *
     * @return ListenerQueue[]
     */
    public function all()
    {
        return $this->invokables;
    }

    /**
     * Add a list of callbacks.
     *
     * @param callable[] $invokables list of callbacks
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function add(array $invokables)
    {
        foreach ($invokables as $index => $invokable) {
            $name = '#'.($index + 1);

            if (!is_callable($invokable)) {
                throw new RuntimeException("The $name value is not callable.");
            }

            $this->addCallback($invokable, $name);
        }
    }

    /**
     * Add a single callback.
     *
     * @param callable $invokable typed callback
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function addCallback(callable $invokable, $name = null)
    {
        $parameter = static::getCallbackType($invokable);

        if (!is_string($parameter)) {
            throw new RuntimeException(
                'Passed callback '.($name ?: gettype($invokable)).
                ' should have at least 1 argument and this first argument must have a typehint.'
            );
        }

        if (!isset($this->invokables[$parameter])) {
            $this->invokables[$parameter] = new ListenerQueue();
        }

        $this->invokables[$parameter]->insert($invokable, 0);
    }

    /**
     * Remove callbacks from the list.
     *
     * @param callable[] $invokables list of callbacks
     */
    public function remove(array $invokables)
    {
        $this->invokables = array_filter(array_map(function (ListenerQueue $queue) use (&$invokables) {
            $filteredQueue = new ListenerQueue();

            foreach ($queue as $invokable) {
                if (!in_array($invokable, $invokables)) {
                    $filteredQueue->insert($invokable, 0);
                }
            }

            return $filteredQueue;
        }, $this->invokables), function (ListenerQueue $queue) {
            return $queue->count();
        });
    }

    /**
     * Remove callbacks from the list by a given class/interface name.
     *
     * @param string $type exact type of a callback argument.
     */
    public function removeByType($type)
    {
        unset($this->invokables[$type]);
    }

    /**
     * Return listeners queues for types that match the given event.
     *
     * @param object $event instance of callback input.
     *
     * @return iterable|ListenerQueue[]
     */
    public function getQueuesByEvent($event)
    {
        foreach ($this->invokables as $type => $invokables) {
            if (is_a($event, $type)) {
                yield $type => $invokables;
            }
        }
    }

    /**
     * Return callbacks for types that match the given event.
     *
     * @param object $event instance of callback input.
     *
     * @return iterable|callable[]
     */
    public function getCallbacksByEvent($event)
    {
        foreach ($this->getQueuesByEvent($event) as $invokables) {
            foreach ($invokables as $invokable) {
                yield $invokable;
            }
        }
    }

    /**
     * Invoke callbacks that match the passed event.
     *
     * @param object $event instance of callback input.
     *
     * @return array
     */
    public function invoke($event)
    {
        $invocations = [];

        foreach ($this->getCallbacksByEvent($event) as $invokable) {
            $invocations[] = $invokable($event);
        }

        return $invocations;
    }

    /**
     * Return the typehint as string of the first argument of a given callback or null if not typed.
     *
     * @param callable $invokable closure or callable
     *
     * @throws ReflectionException
     *
     * @return string|null
     */
    public static function getCallbackType(callable $invokable)
    {
        $reflection = is_array($invokable)
            ? new ReflectionMethod($invokable[0], $invokable[1])
            : new ReflectionFunction($invokable);
        $parameters = $reflection->getParameters();
        $type = null;

        if (count($parameters) && method_exists($parameters[0], 'getType')) {
            $type = $parameters[0]->getType();
        }

        if ($type instanceof ReflectionNamedType) {
            return $type->getName();
        }

        $dump = @strval($parameters[0]);

        return preg_match('/>\s(\S+)\s\$/', $dump, $match) ? $match[1] : null;
    }
}
