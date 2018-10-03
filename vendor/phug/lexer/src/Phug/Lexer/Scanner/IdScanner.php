<?php

/**
 * @example #my-id
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\Token\IdToken;

class IdScanner extends TagScanner
{
    const TOKEN_CLASS = IdToken::class;

    const PATTERN = '#(?<name>[a-zA-Z_][a-zA-Z0-9\-_]*)';
}
