<?php

/**
 * @example mixin my-mixin()
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\MixinToken;

class MixinScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        foreach ($state->scanToken(
            MixinToken::class,
            $state->getOption('mixin_keyword')."[\t ]+(?<name>[a-zA-Z_][a-zA-Z0-9\-_]*)"
        ) as $token) {
            yield $token;

            $reader = $state->getReader();
            if ($reader->match('[\t ]+(?=\()')) {
                $reader->consume();
            }

            foreach ($state->scan(SubScanner::class) as $subToken) {
                yield $subToken;
            }
        }
    }
}
