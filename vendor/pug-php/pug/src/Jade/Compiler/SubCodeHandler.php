<?php

namespace Jade\Compiler;

/**
 * Class Jade\Compiler\SubCodeHandler.
 */
class SubCodeHandler
{
    protected $codeHandler;
    protected $input;
    protected $name;

    public function __construct(CodeHandler $codeHandler, $input, $name)
    {
        $this->codeHandler = $codeHandler;
        $this->input = $input;
        $this->name = $name;
    }

    public function getMiddleString()
    {
        $input = $this->input;

        return function ($start, $end) use ($input) {
            $offset = $start[1] + strlen($start[0]);

            return substr($input, $offset, isset($end) ? $end[1] - $offset : strlen($input));
        };
    }

    public function handleRecursion(&$result)
    {
        $getMiddleString = $this->getMiddleString();
        $codeHandler = $this->codeHandler;

        return function ($arg, $name = '') use (&$result, $codeHandler, $getMiddleString) {
            list($start, $end) = $arg;
            $str = trim($getMiddleString($start, $end));

            if (!strlen($str)) {
                return '';
            }

            $innerCode = $codeHandler->innerCode($str, $name);

            if (count($innerCode) > 1) {
                $result = array_merge($result, array_slice($innerCode, 0, -1));

                return array_pop($innerCode);
            }

            return $innerCode[0];
        };
    }

    protected function handleNestedExpression(&$result)
    {
        $handleRecursion = $this->handleRecursion($result);
        $name = $this->name;

        return function (&$arguments, $start, $end) use ($name, $handleRecursion) {
            if ($end !== false && $start[1] !== $end[1]) {
                array_push(
                    $arguments,
                    $handleRecursion(
                        array($start, $end),
                        intval($name) * 10 + count($arguments)
                    )
                );
            }
        };
    }

    protected function scanSeparators(&$separators, &$result)
    {
        $handleNested = $this->handleNestedExpression($result);

        return function (&$arguments, $open, $close) use (&$separators, $handleNested) {
            $count = 1;

            do {
                $start = current($separators);

                do {
                    $curr = next($separators);

                    if ($curr[0] === $open) {
                        $count++;
                    }
                    if ($curr[0] === $close) {
                        $count--;
                    }
                } while ($curr[0] !== null && $count > 0 && $curr[0] !== ',');

                $handleNested($arguments, $start, current($separators));
            } while ($curr !== false && $count > 0);

            return $count;
        };
    }

    public function handleCodeInbetween(&$separators, &$result)
    {
        $scanSeparators = $this->scanSeparators($separators, $result);
        $input = $this->input;

        return function () use (&$separators, $input, $scanSeparators) {
            $arguments = array();

            $start = current($separators);
            $endPair = array(
                '[' => ']',
                '{' => '}',
                '(' => ')',
                ',' => false,
            );
            $open = $start[0];
            $close = $endPair[$start[0]];

            $count = $scanSeparators($arguments, $open, $close);

            if ($close && $count > 0) {
                throw new \ErrorException($input . "\nMissing closing: " . $close, 14);
            }

            if (($sep = current($separators)) !== false) {
                $end = next($separators);
                if ($end[0] === null && $sep[1] < $end[1]) {
                    $key = count($arguments) - 1;
                    $arguments[$key] .= substr($input, $sep[1] + 1, $end[1] - $sep[1] - 1);
                }
            }

            return $arguments;
        };
    }

    public function getNext($separators)
    {
        return function ($index) use ($separators) {
            if (isset($separators[$index + 1])) {
                return $separators[$index + 1];
            }
        };
    }

    public function addToOutput(&$output, &$key, &$value)
    {
        return function () use (&$output, &$key, &$value) {
            foreach (array('key', 'value') as $var) {
                ${$var} = trim(${$var});
                if (empty(${$var})) {
                    continue;
                }
                if (preg_match('/^\d*[a-zA-Z_]/', ${$var})) {
                    ${$var} = var_export(${$var}, true);
                }
            }
            $output[] = empty($value)
                ? $key
                : $key . ' => ' . $value;
            $key = '';
            $value = null;
        };
    }

    public function consume()
    {
        return function (&$argument, $start) {
            $argument = substr($argument, strlen($start));
        };
    }
}
