<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;
use Phug\Lexer\EscapeTokenInterface;
use Phug\Util\Partial\CheckTrait;
use Phug\Util\Partial\EscapeTrait;
use Phug\Util\Partial\NameTrait;
use Phug\Util\Partial\ValueTrait;
use Phug\Util\Partial\VariadicTrait;

class AttributeToken extends AbstractToken implements EscapeTokenInterface
{
    use NameTrait;
    use ValueTrait;
    use EscapeTrait;
    use CheckTrait;
    use VariadicTrait;
}
