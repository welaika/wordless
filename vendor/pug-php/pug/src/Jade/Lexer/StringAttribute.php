<?php

namespace Jade\Lexer;

/**
 * Class Jade\Lexer\StringAttribute.
 */
class StringAttribute
{
    protected $char;

    public function __construct($char)
    {
        $this->char = $char;
    }

    public function parse(AttributesState $states, &$val, &$quote)
    {
        switch ($states->current()) {
            case 'key':
                $states->push('key char');
                break;

            case 'key char':
                $states->pop();
                break;

            case 'string':
                if ($this->char === $quote) {
                    $states->pop();
                }
                $val .= $this->char;
                break;

            default:
                $states->push('string');
                $val .= $this->char;
                $quote = $this->char;
                break;
        }
    }
}
