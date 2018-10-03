<?php

/**
 * @example tag/
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\AutoCloseToken;

class AutoCloseScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        $reader = $state->getReader();

        if (!$reader->match('\\/')) {
            return;
        }

        $token = $state->createToken(AutoCloseToken::class);

        $reader->consume();
        yield $state->endToken($token);
    }
}
