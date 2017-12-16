<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;
use Phug\Util\Partial\NameTrait;

class VariableToken extends AbstractToken
{
    use NameTrait;
}
