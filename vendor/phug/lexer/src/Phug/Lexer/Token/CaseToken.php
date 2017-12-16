<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;
use Phug\Util\Partial\NameTrait;
use Phug\Util\Partial\SubjectTrait;

class CaseToken extends AbstractToken
{
    use NameTrait;
    use SubjectTrait;
}
