<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;
use Phug\Util\Partial\NameTrait;

class MixinCallToken extends AbstractToken
{
    use NameTrait;
}
