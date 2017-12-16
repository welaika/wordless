<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\DoctypeToken;

class DoctypeScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        return $state->scanToken(DoctypeToken::class, "(doctype|!!!)(?!\S)(?: (?<name>[^\n]*))?");
    }
}
