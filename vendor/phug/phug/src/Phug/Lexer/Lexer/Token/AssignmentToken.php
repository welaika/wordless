<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;
use Phug\Util\Partial\NameTrait;

class AssignmentToken extends AbstractToken
{
    use NameTrait;
}
