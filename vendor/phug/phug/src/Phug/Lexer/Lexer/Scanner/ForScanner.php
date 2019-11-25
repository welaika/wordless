<?php

/**
 * @example for $item in $items
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\Token\ForToken;

class ForScanner extends ControlStatementScanner
{
    public function __construct()
    {
        parent::__construct(
            ForToken::class,
            ['for']
        );
    }
}
