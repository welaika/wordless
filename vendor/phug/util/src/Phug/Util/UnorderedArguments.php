<?php

namespace Phug\Util;

use InvalidArgumentException;

/**
 * Class UnorderedArguments.
 */
class UnorderedArguments
{
    /**
     * @var
     */
    protected $arguments;

    /**
     * UnorderedArguments constructor.
     * Store arguments array (tipycally func_get_args()).
     *
     * @param array $arguments
     */
    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * Ask for an optional argument by type then pop and return
     * the first found from the list.
     *
     * @param $type
     *
     * @return mixed
     */
    public function optional($type)
    {
        foreach ($this->arguments as $index => $argument) {
            if (gettype($argument) === $type || is_a($argument, $type, true)) {
                array_splice($this->arguments, $index, 1);

                return $argument;
            }
        }
    }

    /**
     * Ask for an required argument by type then pop and return
     * the first found from the list. If not found, throw an exception.
     *
     * @param $type
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    public function required($type)
    {
        $count = count($this->arguments);
        $argument = $this->optional($type);
        if ($count === count($this->arguments)) {
            throw new InvalidArgumentException('Arguments miss one of the '.$type.' type');
        }

        return $argument;
    }

    /**
     * Throw an exception if all the arguments have not yet been taken.
     *
     * @throws InvalidArgumentException
     */
    public function noMoreArguments()
    {
        if ($count = count($this->arguments)) {
            throw new InvalidArgumentException('You pass '.$count.' unexpected arguments');
        }
    }

    /**
     * Throw an exception if all the arguments except null ones
     * have not yet been taken.
     *
     * @throws InvalidArgumentException
     */
    public function noMoreDefinedArguments()
    {
        $definedArguments = array_filter($this->arguments, function ($argument) {
            return !is_null($argument);
        });

        if ($count = count($definedArguments)) {
            throw new InvalidArgumentException('You pass '.$count.' unexpected not null arguments');
        }
    }
}
