<?php

/**
 * @example #{'h'.$headerLevel} (produce h1 tag if $headerLevel = 1
 */

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

                    foreach ($state->scan(ElementScanner::class) as $subToken) {
                        yield $subToken;
                    }

                    break;
                }
            }
        }
    }
}
