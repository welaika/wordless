<?php

namespace Jade\Compiler;

/**
 * Class Jade CompilerFacade.
 * Expose methods available from compiled jade tempaltes.
 */
abstract class CompilerFacade extends ValuesCompiler
{
    protected static $mixinBlocks = array();

    /**
     * Record a closure as a mixin block during execution jade template time.
     *
     * @param string  mixin name
     * @param string  mixin block treatment
     */
    public static function recordMixinBlock($name, $func = null)
    {
        if (!isset(static::$mixinBlocks[$name])) {
            static::$mixinBlocks[$name] = array();
        }
        array_push(static::$mixinBlocks[$name], $func);
    }

    /**
     * Record a closure as a mixin block during execution jade template time.
     *
     * @param string  mixin name
     * @param string  mixin block treatment
     */
    public static function callMixinBlock($name, $attributes = array())
    {
        if (isset(static::$mixinBlocks[$name]) && is_array($mixinBlocks = static::$mixinBlocks[$name])) {
            $func = end($mixinBlocks);
            if (is_callable($func)) {
                $func($attributes);
            }
        }
    }

    /**
     * Record a closure as a mixin block during execution jade template time
     * and propagate variables.
     *
     * @param string  mixin name
     * @param &array  variables handler propagated from parent scope
     * @param string  mixin block treatment
     */
    public static function callMixinBlockWithVars($name, &$varHandler, $attributes = array())
    {
        if (isset(static::$mixinBlocks[$name]) && is_array($mixinBlocks = static::$mixinBlocks[$name])) {
            $func = end($mixinBlocks);
            if (is_callable($func)) {
                $func($varHandler, $attributes);
            }
        }
    }

    /**
     * End of the record of the mixin block.
     *
     * @param string  mixin name
     */
    public static function terminateMixinBlock($name)
    {
        if (isset(static::$mixinBlocks[$name])) {
            array_pop(static::$mixinBlocks);
        }
    }

    /**
     * Get property from object.
     *
     * @param object $object source object
     * @param mixed  $key    key to retrive from the object or the array
     *
     * @return mixed
     */
    public static function getPropertyFromObject($anything, $key)
    {
        return isset($anything->$key)
            ? $anything->$key
            : (method_exists($anything, $method = 'get' . ucfirst($key))
                ? $anything->$method()
                : (method_exists($anything, $key)
                    ? array($anything, $key)
                    : null
                )
            );
    }

    /**
     * Get property from object or entry from array.
     *
     * @param object|array $anything source
     * @param mixed        $key      key to retrive from the object or the array
     *
     * @return mixed
     */
    public static function getPropertyFromAnything($anything, $key)
    {
        return is_array($anything)
            ? (isset($anything[$key])
                ? $anything[$key]
                : null
            ) : (is_object($anything)
                ? static::getPropertyFromObject($anything, $key)
                : null
            );
    }

    /**
     * Merge given attributes such as tag attributes with mixin attributes.
     *
     * @param array $attributes
     * @param array $mixinAttributes
     *
     * @return array
     */
    public static function withMixinAttributes($attributes, $mixinAttributes)
    {
        foreach ($mixinAttributes as $attribute) {
            if ($attribute['name'] === 'class') {
                $value = static::joinAny($attribute['value']);
                $attributes['class'] = empty($attributes['class'])
                    ? $value
                    : static::joinAny($attributes['class']) . ' ' . $value;
            }
        }
        if (isset($attributes['class'])) {
            $attributes['class'] = implode(' ', array_unique(explode(' ', $attributes['class'])));
        }

        return $attributes;
    }

    /**
     * Display a list of attributes with the given quote character in HTML.
     *
     * @param array  $attributes
     * @param string $quote
     */
    public static function displayAttributes($attributes, $quote, $terse)
    {
        if (is_array($attributes) || $attributes instanceof Traversable) {
            foreach ($attributes as $key => $value) {
                if ($key !== 'class' && $value !== false && $value !== 'null') {
                    if ($value === true) {
                        echo ' ' . $key . ($terse ? '' : '=' . $quote . $key . $quote);
                        continue;
                    }
                    echo ' ' . $key . '=' . $quote . htmlspecialchars($value) . $quote;
                }
            }
        }
    }

    /**
     * Return true if the given value can be display
     * (null or false should not be displayed in the output HTML).
     *
     * @param $value
     *
     * @return bool
     */
    public static function isDisplayable($value)
    {
        return !is_null($value) && $value !== false;
    }
}
