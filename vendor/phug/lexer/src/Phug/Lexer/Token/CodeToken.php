<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;
use Phug\Util\Partial\BlockTrait;

class CodeToken extends AbstractToken
{
    use BlockTrait;
}
