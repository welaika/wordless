<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;
use Phug\Util\Partial\ModeTrait;
use Phug\Util\Partial\NameTrait;

class BlockToken extends AbstractToken
{
    use NameTrait;
    use ModeTrait;
}
