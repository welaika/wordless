<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;
use Phug\Util\Partial\PairTrait;
use Phug\Util\Partial\SubjectTrait;

class EachToken extends AbstractToken
{
    use SubjectTrait;
    use PairTrait;
}
