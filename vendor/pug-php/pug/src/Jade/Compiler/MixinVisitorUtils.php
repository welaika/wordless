<?php

namespace Jade\Compiler;

use Jade\Lexer\Scanner;

abstract class MixinVisitorUtils extends CodeVisitor
{
    protected function getMixinArgumentAssign($argument)
    {
        $argument = trim($argument);

        if (preg_match('`^[a-zA-Z][a-zA-Z0-9:_-]*\s*=`', $argument)) {
            return explode('=', $argument, 2);
        }
    }

    protected function splitArguments($argumentsString)
    {
        $arguments = array();
        if (is_string($argumentsString) && strlen($argumentsString)) {
            while (preg_match(
                '/^((?:[^,"\'\\(\\)]+|' . Scanner::QUOTED_STRING . '|' . Scanner::PARENTHESES . ')*),/',
                $argumentsString,
                $matches
            )) {
                $arguments[] = trim($matches[1]);
                $argumentsString = trim(substr($argumentsString, strlen($matches[0])));
            }
            $arguments[] = trim($argumentsString);
        }

        return $arguments;
    }

    protected function checkForNewKey(&$arguments, &$argument, &$newArrayKey, $key)
    {
        if (is_null($newArrayKey)) {
            $newArrayKey = $key;
            $argument = array();

            return;
        }

        unset($arguments[$key]);
    }

    protected function parseMixinArguments(&$arguments, &$containsOnlyArrays, &$defaultAttributes)
    {
        $newArrayKey = null;
        $arguments = $this->splitArguments($arguments);
        foreach ($arguments as $key => &$argument) {
            if ($tab = $this->getMixinArgumentAssign($argument)) {
                $this->checkForNewKey($arguments, $argument, $newArrayKey, $key);
                $defaultAttributes[] = var_export($tab[0], true) . ' => ' . $tab[1];
                $arguments[$newArrayKey][$tab[0]] = static::decodeValue($tab[1]);
                continue;
            }

            $containsOnlyArrays = false;
            $newArrayKey = null;
        }

        return array_map(function ($argument) {
            if (is_array($argument)) {
                $argument = var_export($argument, true);
            }

            return $argument;
        }, $arguments);
    }

    protected function parseMixinStringAttribute($data)
    {
        $value = is_array($data['value'])
            ? preg_split('`\s+`', trim(implode(' ', $data['value'])))
            : trim($data['value']);

        return $data['escaped'] === true
            ? is_array($value)
                ? array_map('htmlspecialchars', $value)
                : htmlspecialchars($value)
            : $value;
    }

    protected function parseMixinAttribute($data)
    {
        if ($data['value'] === 'null' || $data['value'] === 'undefined' || is_null($data['value'])) {
            return;
        }

        if (is_bool($data['value'])) {
            return $data['value'];
        }

        return $this->parseMixinStringAttribute($data);
    }

    protected function parseMixinAttributes($attributes, $defaultAttributes, $mixinAttributes)
    {
        if (!count($attributes)) {
            return "(isset(\$attributes)) ? \$attributes : array($defaultAttributes)";
        }

        $parsedAttributes = array();
        foreach ($attributes as $data) {
            $parsedAttributes[$data['name']] = $this->parseMixinAttribute($data);
        }

        $attributes = var_export($parsedAttributes, true);
        $mixinAttributes = var_export(static::decodeAttributes($mixinAttributes), true);

        return "array_merge(\\Jade\\Compiler::withMixinAttributes($attributes, $mixinAttributes), (isset(\$attributes)) ? \$attributes : array($defaultAttributes))";
    }

    protected function renderClosureOpenning()
    {
        $arguments = func_get_args();
        $begin = array_shift($arguments);
        $begin = is_array($begin)
            ? $begin[0] . 'function ' . $begin[1]
            : $begin . 'function ';
        $params = implode(', ', array_map(function ($name) {
            return (substr($name, 0, 1) === '$' ? '' : '$') . $name;
        }, $arguments));

        if ($this->restrictedScope) {
            return $this->buffer($this->createCode($begin . '(' . $params . ') {'));
        }

        $params = '&$__varHandler, ' . $params;

        $this->buffer(
            $this->createCode($begin . '(' . $params . ') {') .
            $this->createCode($this->indent() . 'extract($__varHandler, EXTR_SKIP);')
        );
    }

    protected function renderClosureClosing($code, $arguments = array())
    {
        if (!$this->restrictedScope) {
            $arguments = array_filter(array_map(function ($argument) {
                $argument = explode('=', $argument);
                $argument = trim($argument[0]);

                return substr($argument, 0, 1) === '$'
                    ? substr($argument, 1)
                    : false;
            }, array_slice($arguments, 1)));
            $exception = count($arguments)
                ? ' && !in_array($key, ' . var_export($arguments, true) . ')'
                : '';
            $this->buffer($this->createCode(
                'foreach ($__varHandler as $key => &$val) {' .
                'if ($key !== \'__varHandler\'' . $exception . ') {' .
                '$val = ${$key};' .
                '}' .
                '}'
            ));
        }

        $this->buffer($this->createCode($code));
    }

    protected function visitMixinCodeAndBlock($name, $block, $arguments)
    {
        $this->renderClosureOpenning(
            $this->allowMixinOverride
                ? "{$name} = "
                : array("if(!function_exists('{$name}')) { ", $name),
            implode(',', $arguments)
        );
        $this->indents++;
        $this->visit($block);
        $this->indents--;
        $this->renderClosureClosing($this->allowMixinOverride ? '};' : '} }', $arguments);
    }
}
