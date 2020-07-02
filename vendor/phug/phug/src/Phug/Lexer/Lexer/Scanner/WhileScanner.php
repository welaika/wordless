<?php

/**
 * @example while something_is_true()
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\Token\WhileToken;

class WhileScanner extends ControlStatementScanner
{
    public function __construct()
    {
        parent::__construct(
            WhileToken::class,
            ['while']
        );
    }
}
