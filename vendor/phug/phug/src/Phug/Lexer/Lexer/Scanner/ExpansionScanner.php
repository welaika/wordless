<?php

/**
 * @example li: a
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\ExpansionToken;

class ExpansionScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        $reader = $state->getReader();

        if (!$reader->peekChar(':')) {
            return;
        }

        $token = $state->createToken(ExpansionToken::class);
        $reader->consume();

        //Allow any kind of spacing after an expansion
        $reader->readIndentation();

        yield $state->endToken($token);
    }
}
