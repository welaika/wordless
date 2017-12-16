<?php

namespace Phug\Util\Partial;

use ArrayObject;
use Traversable;

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

    private function isTraversable($value)
    {
        return is_array($value) || $value instanceof Traversable;
    }

    private function filterTraversable($values)
    {
        return array_filter($values, [$this, 'isTraversable']);
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
                        ? $functionName($base[$name], $value)
                        : $value;
                });
            }
        }

        return $this;
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
        return $this->setOptionArrays(func_get_args(), 'array_replace');
    }

    /**
     * {@inheritdoc}
     */
    public function setOptionsRecursive($options)
    {
        return $this->setOptionArrays(func_get_args(), 'array_replace_recursive');
    }

    private function setDefaultOption($key, $value)
    {
        if (!$this->hasOption($key)) {
            $this->setOption($key, $value);
        } elseif (is_array($option = $this->getOption($key)) &&
            (!count($option) || is_string(key($option))) && is_array($value)
        ) {
            $this->setOption($key, array_replace_recursive($value, $this->getOption($key)));
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
                if (!array_key_exists($key, $variable)) {
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
            return $this->isTraversable($options) && array_key_exists($name, $options);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name)
    {
        return $this->withOptionReference($name, function (&$options, $name) {
            return $options[$name];
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
