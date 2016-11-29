<?php

namespace Jade\Compiler;

use Jade\Nodes\Mixin;

abstract class MixinVisitor extends CodeVisitor
{
    protected function getMixinArgumentAssign($argument)
    {
        $argument = trim($argument);

        if (preg_match('`^[a-zA-Z][a-zA-Z0-9:_-]*\s*=`', $argument)) {
            return explode('=', $argument, 2);
        }
    }

    protected function parseMixinArguments(&$arguments, &$containsOnlyArrays, &$defaultAttributes)
    {
        $newArrayKey = null;
        $arguments = is_null($arguments) ? array() : explode(',', $arguments);
        foreach ($arguments as $key => &$argument) {
            if ($tab = $this->getMixinArgumentAssign($argument)) {
                if (is_null($newArrayKey)) {
                    $newArrayKey = $key;
                    $argument = array();
                } else {
                    unset($arguments[$key]);
                }

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

    /**
     * @param Nodes\Mixin $mixin
     */
    protected function visitMixinCall(Mixin $mixin, $name, $blockName, $attributes)
    {
        $arguments = $mixin->arguments;
        $block = $mixin->block;
        $defaultAttributes = array();
        $containsOnlyArrays = true;
        $arguments = $this->parseMixinArguments($mixin->arguments, $containsOnlyArrays, $defaultAttributes);

        $defaultAttributes = implode(', ', $defaultAttributes);
        $attributes = $this->parseMixinAttributes($attributes, $defaultAttributes, $mixin->attributes);

        if ($block) {
            $this->renderClosureOpenning("\\Jade\\Compiler::recordMixinBlock($blockName, ", 'attributes');
            $this->visit($block);
            $this->renderClosureClosing('});');
        }

        $strings = array();
        $arguments = preg_replace_callback(
            '#([\'"])(.*(?!<\\\\)(?:\\\\{2})*)\\1#U',
            function ($match) use (&$strings) {
                $nextIndex = count($strings);
                $strings[] = $match[0];

                return 'stringToReplaceBy' . $nextIndex . 'ThCapture';
            },
            $arguments
        );
        $arguments = array_map(
            function ($arg) use ($strings) {
                return preg_replace_callback(
                    '#stringToReplaceBy([0-9]+)ThCapture#',
                    function ($match) use ($strings) {
                        return $strings[intval($match[1])];
                    },
                    $arg
                );
            },
            $arguments
        );

        array_unshift($arguments, $attributes);
        $arguments = array_filter($arguments, 'strlen');
        $statements = $this->apply('createStatements', $arguments);

        $variables = array_pop($statements);
        if ($mixin->call && $containsOnlyArrays) {
            array_splice($variables, 1, 0, array('null'));
        }
        $variables = implode(', ', $variables);
        array_push($statements, $variables);

        $arguments = $statements;

        $paramsPrefix = '';
        if (!$this->restrictedScope) {
            $this->buffer($this->createCode('$__varHandler = get_defined_vars();'));
            $paramsPrefix = '$__varHandler, ';
        }
        $codeFormat = str_repeat('%s;', count($arguments) - 1) . "{$name}({$paramsPrefix}%s)";

        array_unshift($arguments, $codeFormat);

        $this->buffer($this->apply('createCode', $arguments));
        if (!$this->restrictedScope) {
            $this->buffer(
                $this->createCode(
                    'extract(array_diff_key($__varHandler, array(\'__varHandler\' => 1, \'attributes\' => 1)));'
                )
            );
        }

        if ($block) {
            $code = $this->createCode("\\Jade\\Compiler::terminateMixinBlock($blockName);");
            $this->buffer($code);
        }
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

    /**
     * @param Nodes\Mixin $mixin
     */
    protected function visitMixinDeclaration(Mixin $mixin, $name)
    {
        $arguments = $mixin->arguments;
        $block = $mixin->block;
        $previousVisitedMixin = isset($this->visitedMixin) ? $this->visitedMixin : null;
        $this->visitedMixin = $mixin;
        if ($arguments === null || empty($arguments)) {
            $arguments = array();
        } elseif (!is_array($arguments)) {
            $arguments = array($arguments);
        }

        array_unshift($arguments, 'attributes');
        $arguments = implode(',', $arguments);
        $arguments = explode(',', $arguments);
        array_walk($arguments, array(get_class(), 'initArgToNull'));
        $this->visitMixinCodeAndBlock($name, $block, $arguments);

        if (is_null($previousVisitedMixin)) {
            unset($this->visitedMixin);

            return;
        }

        $this->visitedMixin = $previousVisitedMixin;
    }

    /**
     * @param Nodes\Mixin $mixin
     */
    protected function visitMixin(Mixin $mixin)
    {
        $name = strtr($mixin->name, '-', '_') . '_mixin';
        $blockName = var_export($mixin->name, true);
        if ($this->allowMixinOverride) {
            $name = '$GLOBALS[\'' . $name . '\']';
        }
        $attributes = static::decodeAttributes($mixin->attributes);

        if ($mixin->call) {
            $this->visitMixinCall($mixin, $name, $blockName, $attributes);

            return;
        }

        $this->visitMixinDeclaration($mixin, $name);
    }
}
