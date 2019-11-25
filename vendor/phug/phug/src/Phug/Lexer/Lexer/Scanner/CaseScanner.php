<?php

/**
 * @example case ... when
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\Token\CaseToken;

class CaseScanner extends ControlStatementScanner
{
    public function __construct()
    {
        parent::__construct(
            CaseToken::class,
            ['case']
        );
    }
}
