<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;

class InterpolationStartToken extends AbstractToken
{
    /**
     * @var InterpolationEndToken
     */
    private $end;

    /**
     * @return InterpolationEndToken
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param InterpolationEndToken $end
     */
    public function setEnd(InterpolationEndToken $end)
    {
        $this->end = $end;
    }
}
