<?php

/**
 * @example .my-class
 */

namespace Phug\Lexer\Scanner;

use Phug\Lexer\Token\ClassToken;

class ClassScanner extends TagScanner
{
    const TOKEN_CLASS = ClassToken::class;

    const PATTERN = '\.(?<name>[a-z0-9\-_]+)';
}
