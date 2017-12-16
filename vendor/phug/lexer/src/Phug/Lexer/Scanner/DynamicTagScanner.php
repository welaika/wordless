<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\EscapeTokenInterface;
use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\InterpolationEndToken;

class DynamicTagScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        $reader = $state->getReader();

        if ($reader->match('(?<raw>\!)?#\{')) {
            $raw = $reader->getMatch('raw') === '!';
            if ($raw) {
                $reader->consume(1);
            }

            foreach ($state->scan(InterpolationScanner::class) as $token) {
                if ($raw && $token instanceof EscapeTokenInterface) {
                    $token->unescape();
                }

                yield $token;

                if ($token instanceof InterpolationEndToken) {
                    $reader->consume();

                    foreach ($state->scan(ClassScanner::class) as $subToken) {
                        yield $subToken;
                    }

                    foreach ($state->scan(IdScanner::class) as $subToken) {
                        yield $subToken;
                    }

                    foreach ($state->scan(AutoCloseScanner::class) as $subToken) {
                        yield $subToken;
                    }

                    if ($reader->match('[\t ]')) {
                        foreach ($state->scan(SubScanner::class) as $subToken) {
                            yield $subToken;
                        }
                    }

                    break;
                }
            }
        }
    }
}
