<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;
use Phug\Util\Partial\NameTrait;
use Phug\Util\Partial\PathTrait;

class ImportToken extends AbstractToken
{
    use NameTrait;
    use PathTrait;
}
