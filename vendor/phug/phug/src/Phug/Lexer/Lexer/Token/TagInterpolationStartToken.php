<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;

class TagInterpolationStartToken extends AbstractToken
{
    /**
     * @var TagInterpolationEndToken
     */
    private $end;

    /**
     * @return TagInterpolationEndToken
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param TagInterpolationEndToken $end
     */
    public function setEnd(TagInterpolationEndToken $end)
    {
        $this->end = $end;
    }
}
