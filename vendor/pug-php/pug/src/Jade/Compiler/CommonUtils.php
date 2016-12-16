<?php

namespace Jade\Compiler;

/**
 * Class Jade CommonUtils.
 * Common static methods for compiler and lexer classes.
 */
class CommonUtils
{
    /**
     * @param string $call
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function addDollarIfNeeded($call)
    {
        if ($call === 'Inf') {
            throw new \InvalidArgumentException($call . ' cannot be read from PHP', 16);
        }
        if ($call === 'undefined') {
            return 'null';
        }
        $firstChar = substr($call, 0, 1);
        if (
            !in_array($firstChar, array('$', '\\')) &&
            !preg_match('#^(?:' . CompilerConfig::VARNAME . '\\s*\\(|(?:null|false|true)(?![a-z]))#i', $call) &&
            preg_match('#^(_*' . CompilerConfig::VARNAME . ')(?!\()#', $call)
        ) {
            $call = '$' . $call;
        }

        return $call;
    }

    /**
     * Return true if the ending quote of the string is escaped.
     *
     * @param string $quotedString
     *
     * @return bool
     */
    public static function escapedEnd($quotedString)
    {
        $end = substr($quotedString, strlen(rtrim($quotedString, '\\')));

        return substr($end, 0, 1) === '\\' && strlen($end) & 1;
    }

    /**
     * Return true if the ending quote of the string is escaped.
     *
     * @param object|array $anything    object or array (PHP >= 7) that contains a callable
     * @param string|int   $key|$method key or method name
     * @param bool         $isMethod    true if the second argument is a method
     *
     * @return string
     */
    public static function getGetter($anything, $key)
    {
        return '\\Jade\\Compiler::getPropertyFromAnything(' .
                static::addDollarIfNeeded($anything) . ', ' .
                var_export($key, true) .
            ')';
    }
}
