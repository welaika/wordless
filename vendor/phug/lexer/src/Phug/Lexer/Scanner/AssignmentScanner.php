<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\AssignmentToken;

class AssignmentScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        return $state->scanToken(
            AssignmentToken::class,
            '&(?<name>[a-zA-Z_][a-zA-Z0-9\-_]*)'
        );
    }
}
