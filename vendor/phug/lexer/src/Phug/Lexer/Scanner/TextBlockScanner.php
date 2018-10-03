<?php

/**
 * Call SubScanner and filter indent/outdent tokens.
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\OutdentToken;

class TextBlockScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        foreach ($state->scan(SubScanner::class) as $token) {
            if ($token instanceof IndentToken || $token instanceof OutdentToken) {
                continue;
            }

            yield $token;
        }
    }
}
