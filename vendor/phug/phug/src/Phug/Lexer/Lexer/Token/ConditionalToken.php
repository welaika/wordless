<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;
use Phug\Util\Partial\NameTrait;
use Phug\Util\Partial\SubjectTrait;

class ConditionalToken extends AbstractToken
{
    use NameTrait;
    use SubjectTrait;
}
