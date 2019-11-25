<?php

/**
 * Grouping of ClassScanner, IdScanner, AutoCloseScanner and SubScanner.
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\ScannerInterface;
use Phug\Lexer\State;

class ElementScanner implements ScannerInterface
{
    public function scan(State $state)
    {
        foreach ($state->scan(ClassScanner::class) as $subToken) {
            yield $subToken;
        }

        foreach ($state->scan(IdScanner::class) as $subToken) {
            yield $subToken;
        }

        foreach ($state->scan(AutoCloseScanner::class) as $subToken) {
            yield $subToken;
        }

        foreach ($state->scan(SubScanner::class) as $subToken) {
            yield $subToken;
        }
    }
}
