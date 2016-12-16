<?php

namespace Jade\Lexer;

use Jade\Compiler\CommonUtils;

/**
 * Class Jade\Lexer\Attributes.
 */
class Attributes
{
    protected $token;

    public function __construct($token = null)
    {
        $this->token = $token;
    }

    protected function parseSpace($states, $escapedAttribute, &$key, &$val, $char, $previousNonBlankChar, $nextChar)
    {
        if (
            in_array($states->current(), array('expr', 'array', 'string', 'object')) ||
            (
                ($char === ' ' || $char === "\t") &&
                (
                    !preg_match('/^[a-zA-Z0-9_\\x7f-\\xff"\'\\]\\)\\}]$/', $previousNonBlankChar) ||
                    !preg_match('/^[a-zA-Z0-9_]$/', $nextChar)
                )
            )
        ) {
            $val .= $char;

            return;
        }

        $states->push('key');
        $val = trim($val);
        $key = trim($key);

        if (empty($key)) {
            return;
        }

        $key = preg_replace(
            array('/^[\'\"]|[\'\"]$/', '/\!/'), '', $key
        );
        $this->token->escaped[$key] = $escapedAttribute;

        $this->token->attributes[$key] = ('' === $val) ? true : $this->interpolate($val);

        $key = '';
        $val = '';
    }

    protected function replaceInterpolationsInStrings($match)
    {
        $quote = $match[1];

        return str_replace('\\#{', '#{', preg_replace_callback('/(?<!\\\\)#{([^}]+)}/', function ($match) use ($quote) {
            return $quote . ' . ' . CommonUtils::addDollarIfNeeded(preg_replace_callback(
                    '/(?<![a-zA-Z0-9_\$])(\$?[a-zA-Z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*)(?![a-zA-Z0-9_])/',
                    function ($match) {
                        return CommonUtils::getGetter($match[1], $match[2]);
                    },
                    $match[1]
                )) . ' . ' . $quote;
        }, $match[0]));
    }

    protected function interpolate($attr)
    {
        return preg_replace_callback('/([\'"]).*?(?<!\\\\)(?:\\\\\\\\)*\1/', array($this, 'replaceInterpolationsInStrings'), $attr);
    }

    protected function parseEqual($states, &$escapedAttribute, &$key, &$val, $char, $previousChar)
    {
        switch ($states->current()) {
            case 'key char':
                $key .= $char;
                break;

            case 'val':
            case 'expr':
            case 'array':
            case 'string':
            case 'object':
                $val .= $char;
                break;

            default:
                $escapedAttribute = '!' !== $previousChar;
                $states->push('val');
        }
    }

    protected function parsePairs($states, $char, &$val)
    {
        switch ($char) {
            case '(':
                $states->pushFor('expr', 'val', 'expr');
                break;

            case ')':
                $states->popFor('val', 'expr');
                break;

            case '{':
                $states->pushFor('object', 'val');
                break;

            case '}':
                $states->popFor('object');
                break;

            case '[':
                $states->pushFor('array', 'val');
                break;

            case ']':
                $states->popFor('array');
                break;

            default:
                return false;
        }
        $val .= $char;

        return true;
    }

    protected function parseString(&$states, &$key, &$val, &$quote, $char)
    {
        if (($char === '"' || $char === "'") && !CommonUtils::escapedEnd($val)) {
            $stringParser = new StringAttribute($char);
            $stringParser->parse($states, $val, $quote);

            return;
        }
        ${in_array($states->current(), array('key', 'key char')) ? 'key' : 'val'} .= $char;
    }

    public function parseChar($char, &$nextChar, &$key, &$val, &$quote, $states, &$escapedAttribute, &$previousChar, &$previousNonBlankChar)
    {
        if ($this->parsePairs($states, $char, $val)) {
            return;
        }

        switch ($char) {
            case ',':
            case "\n":
            case "\t":
            case ' ':
                $this->parseSpace($states, $escapedAttribute, $key, $val, $char, $previousNonBlankChar, $nextChar);
                break;

            case '=':
                $this->parseEqual($states, $escapedAttribute, $key, $val, $char, $previousChar);
                break;

            default:
                $this->parseString($states, $key, $val, $quote, $char);
        }
    }

    protected function getParseFunction(&$key, &$val, &$quote, $states, &$escapedAttribute, &$previousChar, &$previousNonBlankChar, $parser)
    {
        return function ($char, $nextChar = '') use (&$key, &$val, &$quote, $states, &$escapedAttribute, &$previousChar, &$previousNonBlankChar, $parser) {
            $parser->parseChar($char, $nextChar, $key, $val, $quote, $states, $escapedAttribute, $previousChar, $previousNonBlankChar);
            $previousChar = $char;
            if (trim($char) !== '') {
                $previousNonBlankChar = $char;
            }
        };
    }

    /**
     * @return object
     */
    public function parseWith($str)
    {
        $parser = $this;

        $key = '';
        $val = '';
        $quote = '';
        $states = new AttributesState();
        $escapedAttribute = '';
        $previousChar = '';
        $previousNonBlankChar = '';

        $parse = $this->getParseFunction($key, $val, $quote, $states, $escapedAttribute, $previousChar, $previousNonBlankChar, $parser);

        for ($i = 0; $i < strlen($str); $i++) {
            $parse(substr($str, $i, 1), substr($str, $i + 1, 1));
        }

        $parse(',');
    }
}
