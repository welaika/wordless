<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\BlockToken;

class BlockScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        foreach ($state->scanToken(
            BlockToken::class,
            'block(?:[\t ]+(?<mode>append|prepend|replace))?'.
            '(?:[\t ]+(?<name>[a-zA-Z_][a-zA-Z0-9\-_]*))?(?![a-zA-Z0-9\-_])'
        ) as $token) {
            if ($token instanceof BlockToken && empty($token->getMode())) {
                $token->setMode('replace');
            }

            yield $token;

            foreach ($state->scan(SubScanner::class) as $subToken) {
                yield $subToken;
            }
        }

        foreach ($state->scanToken(
            BlockToken::class,
            '(?<mode>append|prepend|replace)'.
            '(?:[\t ]+(?<name>[a-zA-ZA-Z][a-zA-Z0-9\-_]*))(?![a-zA-Z0-9\-_])'
        ) as $token) {
            yield $token;

            foreach ($state->scan(SubScanner::class) as $subToken) {
                yield $subToken;
            }
        }
    }
}
