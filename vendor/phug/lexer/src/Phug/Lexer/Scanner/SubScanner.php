<?php

/**
 * Scanner for after-tag contents (text or expansion).
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\EscapeTokenInterface;
use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;

class SubScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        $reader = $state->getReader();

        //Text block on tags etc. (p. some text|p!. some text)
        if ($reader->match('(\\!?)\\.(?=\\s)')) {
            $escape = $reader->getMatch(1) === '!';
            $reader->consume();

            foreach ($state->scan(MultilineScanner::class) as $token) {
                if ($token instanceof EscapeTokenInterface && $escape) {
                    $token->escape();
                }

                yield $token;
            }

            return;
        }

        //Escaped text after e.g. tags, classes (p! some text)
        if ($reader->match('!(?!=)')) {
            $reader->consume();

            foreach ($state->scan(TextScanner::class) as $token) {
                if ($token instanceof EscapeTokenInterface) {
                    $token->escape();
                }

                yield $token;
            }
        }

        foreach ($state->scan(ExpansionScanner::class) as $token) {
            yield $token;
        }
    }
}
