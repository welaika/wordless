<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\VariableToken;

class VariableScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        return $state->scanToken(
            VariableToken::class,
            '\$(?<name>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)'
        );
    }
}
