<?php

namespace Jade\Compiler;

abstract class ValuesCompiler extends CompilerUtils
{
    /**
     * Value treatment if it must not be escaped.
     *
     * @param string  input value
     *
     * @return string
     */
    public static function getUnescapedValue($val)
    {
        if (is_null($val) || $val === false || $val === '') {
            return '';
        }

        return is_array($val) || is_bool($val) || is_int($val) || is_float($val) ? json_encode($val) : strval($val);
    }

    /**
     * Value treatment if it must be escaped.
     *
     * @param string  input value
     *
     * @return string
     */
    public static function getEscapedValue($val, $quote)
    {
        $val = htmlspecialchars(static::getUnescapedValue($val), ENT_NOQUOTES);

        return str_replace($quote, $quote === '"' ? '&quot;' : '&apos;', $val);
    }

    /**
     * Convert style object to CSS string.
     *
     * @param mixed value to be computed into style.
     *
     * @return mixed
     */
    public static function styleValue($val)
    {
        if (is_array($val) && !is_string(key($val))) {
            $val = implode(';', $val);
        } elseif (is_array($val) || is_object($val)) {
            $style = array();
            foreach ($val as $key => $property) {
                $style[] = $key . ':' . $property;
            }

            $val = implode(';', $style);
        }

        return $val;
    }

    /**
     * Convert style object to CSS string and return PHP code to escape then display it.
     *
     * @param mixed value to be computed into style and escaped.
     *
     * @return string
     */
    public static function getEscapedStyle($val, $quote)
    {
        return static::getEscapedValue(static::styleValue($val), $quote);
    }

    /**
     * Convert style object to CSS string and return PHP code to display it.
     *
     * @param mixed value to be computed into style and stringified.
     *
     * @return string
     */
    public static function getUnescapedStyle($val)
    {
        return static::getUnescapedValue(static::styleValue($val));
    }
}
