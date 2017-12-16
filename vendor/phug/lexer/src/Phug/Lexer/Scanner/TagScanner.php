<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\TagToken;

class TagScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        foreach ($state->scanToken(
            TagToken::class,
            '(?<name>[a-zA-Z_][a-zA-Z0-9_]*(?:[:-][a-zA-Z_][a-zA-Z0-9_]*)*)',
            'i'
        ) as $token) {
            yield $token;

            foreach ($state->scan(ClassScanner::class) as $subToken) {
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
