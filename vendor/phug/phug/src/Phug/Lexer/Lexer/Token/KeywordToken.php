<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;
use Phug\Util\Partial\NameTrait;
use Phug\Util\Partial\ValueTrait;

class KeywordToken extends AbstractToken
{
    use NameTrait;
    use ValueTrait;
}
