<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\YieldToken;

class YieldScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        foreach ($state->scanToken(YieldToken::class, 'yield') as $token) {
            yield $token;

            foreach ($state->scan(SubScanner::class) as $subToken) {
                yield $subToken;
            }
        }
    }
}
