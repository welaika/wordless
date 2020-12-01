<?php

namespace Phug\Util\Partial;

use ArrayAccess;
use ArrayObject;
use Phug\Util\Collection;

/**
 * Class OptionTrait.
 */
trait OptionTrait
{
    /**
     * @var ArrayObject
     */
    private $options = null;

    /**
     * @var array
     */
    private $optionNameHandlers = [];

    /**
     * @var callable
     */
    protected $replaceFunction = 'array_replace';

    /**
     * @var callable
     */
    protected $recursiveReplaceFunction = 'array_replace_recursive';

    /**
     * @var string[]
     */
    protected $nonDeepOptions = [
        'shared_variables',
        'globals',
    ];

    /**
     * @param string $name
     *
     * @return string
     */
    private function handleOptionName($name)
    {
        if (is_string($name)) {
            $name = explode('.', $name);
        }

        foreach ($this->optionNameHandlers as $handler) {
            $name = $handler($name);
        }

        return $name;
    }

    private function filterTraversable($values)
    {
        return array_filter($values, [Collection::class, 'isIterable']);
    }

    /**
     * @param array  $arrays
     * @param string $functionName
     *
     * @return $this
     */
    private function setOptionArrays(array $arrays, $functionName)
    {
        if (count($arrays) && !$this->options) {
            $this->options = $arrays[0] instanceof ArrayObject ? $arrays[0] : new ArrayObject($arrays[0]);
        }

        $options = $this->getOptions();

        foreach ($this->filterTraversable($arrays) as $array) {
            foreach ($array as $key => $value) {
                $this->withVariableReference($options, $key, function (&$base, $name) use ($functionName, $value) {
                    $base[$name] = isset($base[$name]) && is_array($base[$name]) && is_array($value)
                        ? $this->mergeOptionValue($name, $base[$name], $value, $functionName)
                        : $value;
                });
            }
        }

        return $this;
    }

    private function mergeOptionValue($name, $current, $addedValue, $functionName)
    {
        if ($functionName === $this->recursiveReplaceFunction && in_array($name, $this->nonDeepOptions)) {
            $functionName = $this->replaceFunction;
        }

        return $functionName($current, $addedValue);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->options = new ArrayObject();
        }

        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions($options)
    {
        return $this->setOptionArrays(func_get_args(), $this->replaceFunction);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionsRecursive($options)
    {
        return $this->setOptionArrays(func_get_args(), $this->recursiveReplaceFunction);
    }

    private function setDefaultOption($key, $value)
    {
        if (!$this->hasOption($key)) {
            $this->setOption($key, $value);
        } elseif (is_array($option = $this->getOption($key)) &&
            (!count($option) || is_string(key($option))) && is_array($value)
        ) {
            $this->setOption(
                $key,
                $this->mergeOptionValue($key, $value, $this->getOption($key), $this->recursiveReplaceFunction)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionsDefaults($options = null)
    {
        $first = $options && !$this->options;
        if ($first) {
            $this->options = $options instanceof ArrayObject ? $options : new ArrayObject($options);
        }
        foreach ($this->filterTraversable(array_slice(func_get_args(), $first ? 1 : 0)) as $array) {
            foreach ($array as $key => $value) {
                $this->setDefaultOption($key, $value);
            }
        }

        return $this;
    }

    /**
     * @param mixed        $variable
     * @param array|string $keys
     * @param callable     $callback
     *
     * @return &$options
     */
    private function withVariableReference(&$variable, $name, $callback)
    {
        $keys = $this->handleOptionName($name);
        if (is_array($keys)) {
            foreach (array_slice($keys, 0, -1) as $key) {
                if (is_array($variable) && !array_key_exists($key, $variable) ||
                    $variable instanceof ArrayAccess && !$variable->offsetExists($key)
                ) {
                    $variable[$key] = [];
                }
                $variable = &$variable[$key];
            }
            $name = end($keys);
        }

        return $callback($variable, $name);
    }

    /**
     * @param array|string $keys
     * @param callable     $callback
     *
     * @return &$options
     */
    private function withOptionReference($name, $callback)
    {
        if (!$this->options) {
            $this->options = new ArrayObject();
        }

        return $this->withVariableReference($this->options, $name, $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption($name)
    {
        return $this->withOptionReference($name, function (&$options, $name) {
            if ($options instanceof ArrayAccess) {
                return $options->offsetExists($name);
            }

            if (is_array($options)) {
                return array_key_exists($name, $options);
            }

            return is_object($options) && property_exists($options, $name);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name)
    {
        return $this->withOptionReference($name, function (&$options, $name) {
            return is_array($options) || $options instanceof ArrayAccess
                ? $options[$name]
                : $options->$name;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($name, $value)
    {
        $this->withOptionReference($name, function (&$options, $name) use ($value) {
            $options[$name] = $value;
        });

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function unsetOption($name)
    {
        $this->withOptionReference($name, function (&$options, $name) {
            unset($options[$name]);
        });

        return $this;
    }

    /**
     * @param callable $handler
     */
    public function addOptionNameHandlers($handler)
    {
        $this->optionNameHandlers[] = $handler;
    }

    public function resetOptions()
    {
        $this->options = null;
    }
}
