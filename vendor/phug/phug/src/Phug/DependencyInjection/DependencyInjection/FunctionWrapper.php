<?php

namespace Phug\DependencyInjection;

use ReflectionFunction;

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
            if ($parameter->isArray()) {
                $string .= 'array ';
            } elseif ($parameter->getClass()) {
                $string .= $parameter->getClass()->name.' ';
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
}
