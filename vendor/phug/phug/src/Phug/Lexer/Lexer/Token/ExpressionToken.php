<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;
use Phug\Lexer\EscapeTokenInterface;
use Phug\Util\Partial\CheckTrait;
use Phug\Util\Partial\EscapeTrait;
use Phug\Util\Partial\ValueTrait;

class ExpressionToken extends AbstractToken implements EscapeTokenInterface
{
    use ValueTrait;
    use EscapeTrait;
    use CheckTrait;
}
