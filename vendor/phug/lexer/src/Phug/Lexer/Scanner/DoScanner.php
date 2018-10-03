<?php

/**
 * /!\ Warning, this is a PHP-specific syntax, this does not exists in pugjs.
 *
 * @example do ... while
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\Token\DoToken;

class DoScanner extends ControlStatementScanner
{
    public function __construct()
    {
        parent::__construct(
            DoToken::class,
            ['do']
        );
    }
}
