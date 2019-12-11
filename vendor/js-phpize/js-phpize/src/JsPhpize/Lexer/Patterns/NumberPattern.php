<?php

namespace JsPhpize\Lexer\Patterns;

use JsPhpize\Lexer\Pattern;

class NumberPattern extends Pattern
{
    public function __construct($priority)
    {
        parent::__construct($priority, 'number', '0[bB][01]+(?:_[01]+)*|0[oO][0-7]+(?:_[0-7]+)*|0[xX][0-9a-fA-F]+(?:_[0-9a-fA-F]+)*|(\d+(?:_\d+)*(\.\d*(?:_\d+)*)?|\.\d+(?:_\d+)*)([eE]-?\d+(?:_\d+)*)?');
    }
}
