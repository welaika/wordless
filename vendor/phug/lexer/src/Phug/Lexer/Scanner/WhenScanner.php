<?php

namespace Phug\Lexer\Scanner;

use Phug\Lexer\Token\WhenToken;

class WhenScanner extends ControlStatementScanner
{
    public function __construct()
    {
        parent::__construct(
            WhenToken::class,
            ['when', 'default']
        );
    }
}
