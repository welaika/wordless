<?php

namespace Phug\DependencyInjection;

use ReflectionFunction;
use ReflectionParameter;

class FunctionWrapper extends ReflectionFunction
{
    /**
     * Dump function parameters as a PHP tupple.
     *
     * @return string
     */
    public function dumpParameters()
    {
        $parameters = [];
        foreach ($this->getParameters() as $parameter) {
            $string = '';
            $type = $this->getTypeAsString($parameter);

            if ($type) {
                $string .= "$type ";
            }

            if ($parameter->isPassedByReference()) {
                $string .= '&';
            }

            $string .= '$'.$parameter->name;

            if ($parameter->isOptional()) {
                $string .= ' = '.var_export($parameter->getDefaultValue(), true);
            }

            $parameters[] = $string;
        }

        return '('.implode(', ', $parameters).')';
    }

    /**
     * Dump function body contained inside brackets.
     *
     * @return string
     */
    public function dumpBody()
    {
        $lines = file($this->getFileName());
        $startLine = $this->getStartLine();
        $endLine = $this->getEndLine();
        $lines[$startLine - 1] = explode('{', $lines[$startLine - 1]);
        $lines[$startLine - 1] = end($lines[$startLine - 1]);
        $end = mb_strrpos($lines[$endLine - 1], '}');
        if ($end !== false) {
            $lines[$endLine - 1] = mb_substr($lines[$endLine - 1], 0, $end);
        }
        $lines[$endLine - 1] .= '}';

        $code = '';
        for ($line = $startLine - 1; $line < $endLine; $line++) {
            $code .= $lines[$line];
        }

        return $code;
    }

    /**
     * Return the type as a string in a way compatible from PHP 5.5 to 8.0.
     *
     * @codeCoverageIgnore
     *
     * @param ReflectionParameter $parameter
     *
     * @return string|null
     */
    protected function getTypeAsString(ReflectionParameter $parameter)
    {
        if (version_compare(PHP_VERSION, '8.0.0-dev', '<')) {
            if ($parameter->isArray()) {
                return 'array';
            }

            $class = $parameter->getClass();

            return $class ? $class->name : null;
        }

        /** @var mixed $parameter ReflectionParameter has hasType and getType methods since PHP 7.0. */

        return $parameter->hasType() ? strval($parameter->getType()) : null;
    }
}
