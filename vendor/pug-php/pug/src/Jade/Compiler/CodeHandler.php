<?php

namespace Jade\Compiler;

use Jade\Lexer\Scanner;

/**
 * Class Jade\Compiler\CodeHandler.
 */
class CodeHandler extends CompilerUtils
{
    protected $compiler;
    protected $input;
    protected $name;
    protected $separators;

    public function __construct($compiler, $input, $name)
    {
        if (!is_string($input)) {
            throw new \InvalidArgumentException('Expecting a string of PHP, got: ' . gettype($input), 11);
        }

        if (strlen($input) === 0) {
            throw new \InvalidArgumentException('Expecting a string of PHP, empty string received.', 12);
        }

        $this->compiler = $compiler;
        $this->input = trim(preg_replace('/\bvar\b/', '', $input));
        $this->name = $name;
        $this->separators = array();
    }

    public function innerCode($input, $name)
    {
        $handler = new static($this->compiler, $input, $name);

        return $handler->parse();
    }

    public function parse()
    {
        if ($this->isQuotedString()) {
            return array($this->input);
        }

        if (strpos('=,;?', substr($this->input, 0, 1)) !== false) {
            throw new \ErrorException('Expecting a variable name or an expression, got: ' . $this->input, 13);
        }

        preg_match_all(
            '/(?<![<>=!])=(?!>|=)|[\\[\\]\\{\\}\\(\\),;\\.]|(?!:):|->/', // punctuation
            preg_replace_callback('/[a-zA-Z0-9\\\\_\\x7f-\\xff]*\\((?:[0-9\\/%\\.,\\s*+-]++|(?R))*+\\)/', function ($match) {
                // no need to keep separators in simple PHP expressions (functions calls, parentheses, calculs)
                return str_repeat(' ', strlen($match[0]));
            }, preg_replace_callback('/' . Scanner::QUOTED_STRING . '/', function ($match) {
                // do not take separators in strings
                return str_repeat(' ', strlen($match[0]));
            }, $this->input)),
            $separators,
            PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE
        );

        $this->separators = $separators[0];

        if (count($this->separators) === 0) {
            if (strstr('0123456789-+("\'$', substr($this->input, 0, 1)) === false) {
                $this->input = $this->compiler->phpizeExpression('addDollarIfNeeded', $this->input);
                //$this->input = static::addDollarIfNeeded($this->input);
            }

            return array($this->input);
        }

        // add a pseudo separator for the end of the input
        array_push($this->separators, array(null, strlen($this->input)));

        return $this->parseBetweenSeparators();
    }

    protected function isQuotedString()
    {
        $firstChar = substr($this->input, 0, 1);
        $lastChar = substr($this->input, -1);

        return false !== strpos('"\'', $firstChar) && $lastChar === $firstChar;
    }

    protected function getVarname($separator)
    {
        // do not add $ if it is not like a variable
        $varname = static::convertVarPath(substr($this->input, 0, $separator[1]), '/^%s/');

        return $separator[0] !== '(' && $varname !== '' && strstr('0123456789-+("\'$', substr($varname, 0, 1)) === false
            ? static::addDollarIfNeeded($varname)
            : $varname;
    }

    protected function parseArrayString(&$argument, $match, $consume, &$quote, &$key, &$value)
    {
        $quote = $quote
            ? CommonUtils::escapedEnd($match[1])
                ? $quote
                : null
            : $match[2];
        ${is_null($value) ? 'key' : 'value'} .= $match[0];
        $consume($argument, $match[0]);
    }

    protected function parseArrayAssign(&$argument, $match, $consume, &$quote, &$key, &$value)
    {
        if ($quote) {
            ${is_null($value) ? 'key' : 'value'} .= $match[0];
            $consume($argument, $match[0]);

            return;
        }

        if (!is_null($value)) {
            throw new \ErrorException('Parse error on ' . substr($argument, strlen($match[1])), 15);
        }

        $key .= $match[1];
        $value = '';
        $consume($argument, $match[0]);
    }

    protected function parseArrayElement(&$argument, $match, $consume, &$quote, &$key, &$value)
    {
        switch ($match[2]) {
            case '"':
            case "'":
                $this->parseArrayString($argument, $match, $consume, $quote, $key, $value);
                break;
            case ':':
            case '=>':
                $this->parseArrayAssign($argument, $match, $consume, $quote, $key, $value);
                break;
            case ',':
                ${is_null($value) ? 'key' : 'value'} .= $match[0];
                $consume($argument, $match[0]);
                break;
        }
    }

    protected function parseArray($input, $subCodeHandler)
    {
        $output = array();
        $key = '';
        $value = null;
        $addToOutput = $subCodeHandler->addToOutput($output, $key, $value);
        $consume = $subCodeHandler->consume();
        foreach ($input as $argument) {
            $argument = ltrim($argument, '$');
            $quote = null;
            while (preg_match('/^(.*?)(=>|[\'",:])/', $argument, $match)) {
                $this->parseArrayElement($argument, $match, $consume, $quote, $key, $value);
            }
            ${is_null($value) ? 'key' : 'value'} .= $argument;
            $addToOutput();
        }

        return 'array(' . implode(', ', $output) . ')';
    }

    protected function parseEqual($sep, &$separators, &$result, $innerName, $subCodeHandler)
    {
        if (preg_match('/^[[:space:]]*$/', $innerName)) {
            next($separators);
            $handleCodeInbetween = $subCodeHandler->handleCodeInbetween($separators, $result);

            return implode($handleCodeInbetween());
        }

        $handleRecursion = $subCodeHandler->handleRecursion($result);

        return $handleRecursion(array($sep, end($separators)));
    }

    protected function parseSeparator($sep, &$separators, &$result, &$varname, $subCodeHandler, $innerName)
    {
        $handleCodeInbetween = $subCodeHandler->handleCodeInbetween($separators, $result);
        $var = '$__' . $this->name;

        switch ($sep[0]) {
            // translate the javascript's obj.attr into php's obj->attr or obj['attr']
            /*
            case '.':
                $result[] = sprintf("%s=is_array(%s)?%s['%s']:%s->%s",
                    $var, $varname, $varname, $innerName, $varname, $innerName
                );
                $varname = $var;
                break;
            //*/

            // funcall
            case '(':
                $arguments = $handleCodeInbetween();
                $call = $varname . '(' . implode(', ', $arguments) . ')';
                $call = static::addDollarIfNeeded($call);
                $varname = $var;
                array_push($result, "{$var}={$call}");
                break;

            case '[':
                if (preg_match('/[a-zA-Z0-9\\\\_\\x7f-\\xff]$/', $varname)) {
                    $varname .= $sep[0] . $innerName;
                    break;
                }
            case '{':
                $varname .= $this->parseArray($handleCodeInbetween(), $subCodeHandler);
                break;

            case '=':
                $varname .= '=' . $this->parseEqual($sep, $separators, $result, $innerName, $subCodeHandler);
                break;

            default:
                if (($innerName !== false && $innerName !== '') || $sep[0] !== ')') {
                    $varname .= $sep[0] . $innerName;
                }
                break;
        }
    }

    protected function parseBetweenSeparators()
    {
        $separators = $this->separators;

        $result = array();

        $varname = $this->getVarname($separators[0]);

        $subCodeHandler = new SubCodeHandler($this, $this->input, $this->name);
        $getMiddleString = $subCodeHandler->getMiddleString();
        $getNext = $subCodeHandler->getNext($separators);

        // using next() ourselves so that we can advance the array pointer inside inner loops
        while (($sep = current($separators)) && $sep[0] !== null) {
            // $sep[0] - the separator string due to PREG_SPLIT_OFFSET_CAPTURE flag or null if end of string
            // $sep[1] - the offset due to PREG_SPLIT_OFFSET_CAPTURE

            $innerName = $getMiddleString($sep, $getNext(key($separators)));

            $this->parseSeparator($sep, $separators, $result, $varname, $subCodeHandler, $innerName);

            next($separators);
        }
        array_push($result, $varname);

        return $result;
    }
}
