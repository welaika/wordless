<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\TextToken;

class TextLineScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        $reader = $state->getReader();

        if (!$reader->match('([!]?)\|')) {
            return;
        }

        $escaped = $reader->getMatch(1) === '!';

        $reader->consume();

        if ($reader->peekNewLine()) {
            $reader->consume();
            /** @var TextToken $text */
            $text = $state->createToken(TextToken::class);
            $text->setValue(' ');

            yield $text;
            yield $state->createToken(NewLineToken::class);

            return;
        }

        foreach ($state->scan(TextScanner::class) as $token) {
            if ($escaped && $token instanceof TextToken) {
                $token->escape();
            }

            yield $token;
        }
    }
}
