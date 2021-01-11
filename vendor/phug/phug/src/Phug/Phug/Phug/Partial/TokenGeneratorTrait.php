<?php

namespace Phug\Partial;

use Exception;
use Phug\Invoker;
use Phug\Util\Collection;
use ReflectionException;

trait TokenGeneratorTrait
{
    /**
     * Get a iterable list of output tokens from a list of interceptors and input tokens.
     *
     * @param callable[] $callbacks list of callable interceptors
     * @param iterable   $tokens    input tokens
     *
     * @throws Exception
     * @throws ReflectionException
     *
     * @return iterable
     */
    protected function getTokenGenerator($callbacks, $tokens)
    {
        if (count($callbacks) === 0) {
            return $tokens;
        }

        $callback = array_shift($callbacks);
        $type = Invoker::getCallbackType($callback);

        return (new Collection($tokens))->yieldFlatMap(function ($token) use ($type, $callback, $callbacks) {
            $result = is_a($token, $type) ? $callback($token) : null;

            return $this->getTokenGenerator($callbacks, $result ?: [$token]);
        });
    }
}
