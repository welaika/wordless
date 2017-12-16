<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\NewLineToken;

class NewLineScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        $reader = $state->getReader();

        if (!$reader->peekNewLine()) {
            return;
        }

        $token = $state->createToken(NewLineToken::class);

        $reader->consume();
        yield $state->endToken($token);
    }
}
