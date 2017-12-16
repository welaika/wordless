<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\ClassToken;

class ClassScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        foreach ($state->scanToken(ClassToken::class, '\.(?<name>[a-z0-9\-_]+)', 'i') as $token) {
            yield $token;

            //Before any sub-tokens (e.g. just '.' to enter a text block), we scan for further classes
            foreach ($state->scan(self::class) as $subToken) {
                yield $subToken;
            }

            foreach ($state->scan(IdScanner::class) as $subToken) {
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
