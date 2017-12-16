<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\IdToken;

class IdScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        foreach ($state->scanToken(IdToken::class, '#(?<name>[a-zA-Z_][a-zA-Z0-9\-_]*)', 'i') as $token) {
            yield $token;

            foreach ($state->scan(ClassScanner::class) as $subToken) {
                yield $subToken;
            }

            foreach ($state->scan(AutoCloseScanner::class) as $subToken) {
                yield $subToken;
            }

            foreach ($state->scan(SubScanner::class) as $subToken) {
                yield $subToken;
            }
        }
    }
}
