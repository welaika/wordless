<?php

namespace Jade\Compiler;

abstract class AttributesCompiler extends CompilerFacade
{
    protected function getAttributeDisplayCode($key, $value, $valueCheck)
    {
        if ($key === 'style') {
            $value = preg_replace('/::get(Escaped|Unescaped)Value/', '::get$1Style', $value, 1);
        }

        return is_null($valueCheck)
            ? ' ' . $key . '=' . $this->quote . $value . $this->quote
            : $this->createCode('if (true === ($__value = %1$s)) { ', $valueCheck)
                . $this->getBooleanAttributeDisplayCode($key)
                . $this->createCode('} else if (\\Jade\\Compiler::isDisplayable($__value)) { ')
                . ' ' . $key . '=' . $this->quote . $value . $this->quote
                . $this->createCode('}');
    }

    protected function getBooleanAttributeDisplayCode($key)
    {
        return ' ' . $key . ($this->terse
            ? ''
            : '=' . $this->quote . $key . $this->quote
        );
    }

    protected function getValueStatement($statements)
    {
        return is_string($statements[0])
            ? $statements[0]
            : $statements[0][0];
    }

    protected function getAndAttributeCode($attr, &$classes, &$classesCheck)
    {
        $addClasses = '" "';
        if (count($classes) || count($classesCheck)) {
            foreach ($classes as &$value) {
                $value = var_export($value, true);
            }
            foreach ($classesCheck as $value) {
                $statements = $this->createStatements($value);
                $classes[] = $statements[0][0];
            }
            $addClasses = '" " . implode(" ", array(' . implode(', ', $classes) . '))';
            $classes = array();
            $classesCheck = array();
        }
        $value = empty($attr['value']) ? 'attributes' : $attr['value'];
        $statements = $this->createStatements($value);

        return $this->createCode(
            '$__attributes = ' . $this->getValueStatement($statements) . ';' .
            'if (is_array($__attributes)) { ' .
                '$__attributes["class"] = trim(' .
                    '$__classes = (empty($__classes) ? "" : $__classes . " ") . ' .
                    '(isset($__attributes["class"]) ? (is_array($__attributes["class"]) ? implode(" ", $__attributes["class"]) : $__attributes["class"]) : "") . ' .
                    $addClasses .
                '); ' .
                'if (empty($__attributes["class"])) { ' .
                    'unset($__attributes["class"]); ' .
                '} ' .
            '} ' .
            '\\Jade\\Compiler::displayAttributes($__attributes, ' . var_export($this->quote, true) . ', ' . var_export($this->terse, true) . ');');
    }

    protected function getClassAttribute($value, &$classesCheck)
    {
        $statements = $this->createStatements($value);
        $value = is_array($statements[0]) ? $statements[0][0] : $statements[0];
        $classesCheck[] = '(is_array($_a = ' . $value . ') ? implode(" ", $_a) : $_a)';

        return $this->keepNullAttributes ? '' : 'null';
    }

    protected function getValueCode($escaped, $value, &$valueCheck)
    {
        if ($this->keepNullAttributes) {
            return $this->escapeIfNeeded($escaped, $value);
        }

        $valueCheck = $value;

        return $this->escapeIfNeeded($escaped, '$__value');
    }

    protected function getAttributeValue($escaped, $key, $value, &$classesCheck, &$valueCheck)
    {
        if ($this->isConstant($value)) {
            $value = trim($value, ' \'"');

            return $value === 'undefined' ? 'null' : $value;
        }

        $json = static::parseValue($value);

        if ($key === 'class') {
            return $json !== null && is_array($json)
                ? implode(' ', $json)
                : $this->getClassAttribute($value, $classesCheck);
        }

        return $this->getValueCode($escaped, $value, $valueCheck);
    }

    protected function escapeValueIfNeeded($value, $escaped, $valueCheck)
    {
        return is_null($valueCheck) && $escaped && !$this->keepNullAttributes
            ? $this->escapeValue($value)
            : $value;
    }

    protected function compileAttributeValue($key, $value, $attr, $valueCheck)
    {
        return $value === true || $attr['value'] === true
            ? $this->getBooleanAttributeDisplayCode($key)
            : ($value !== false && $attr['value'] !== false && $value !== 'null' && $value !== 'undefined'
                ? $this->getAttributeDisplayCode(
                    $key,
                    $this->escapeValueIfNeeded($value, $attr['escaped'], $valueCheck),
                    $valueCheck
                )
                : ''
            );
    }

    protected function getAttributeCode($attr, &$classes, &$classesCheck)
    {
        $key = trim($attr['name']);

        if ($key === '&attributes') {
            return $this->getAndAttributeCode($attr, $classes, $classesCheck);
        }

        $valueCheck = null;
        $value = trim($attr['value']);

        $value = $this->getAttributeValue($attr['escaped'], $key, $value, $classesCheck, $valueCheck);

        if ($key === 'class') {
            if ($value !== 'false' && $value !== 'null' && $value !== 'undefined') {
                array_push($classes, $value);
            }

            return '';
        }

        return $this->compileAttributeValue($key, $value, $attr, $valueCheck);
    }

    protected function getClassesCode(&$classes, &$classesCheck)
    {
        return trim($this->createCode(
            '$__classes = implode(" ", ' .
                'array_unique(array_merge(' .
                    'empty($__classes) ? array() : explode(" ", $__classes), ' .
                    var_export($classes, true) . ', ' .
                    'array(' . implode(', ', $classesCheck) . ')' .
                ')) ' .
            ');'
        ));
    }

    protected function getClassesDisplayCode()
    {
        return trim($this->createCode(
            'if (!empty($__classes)) { ' .
                'echo ' .
                    var_export(
                        ' ' . $this->getOption('classAttribute', 'class') .
                        '=' .
                        $this->quote,
                        true
                    ) . ' . $__classes . ' . var_export($this->quote, true) . '; ' .
            '} ' .
            'unset($__classes); '
        ));
    }

    /**
     * @param array $attributes
     */
    protected function compileAttributes($attributes)
    {
        $items = '';
        $classes = array();
        $classesCheck = array();

        foreach ($attributes as $attr) {
            $items .= $this->getAttributeCode($attr, $classes, $classesCheck);
        }

        $items .= $this->getClassesCode($classes, $classesCheck);

        $this->buffer($items, false);
    }
}
