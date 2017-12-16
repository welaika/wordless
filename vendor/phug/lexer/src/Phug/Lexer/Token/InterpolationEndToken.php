<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;

class InterpolationEndToken extends AbstractToken
{
    /**
     * @var InterpolationStartToken
     */
    private $start;

    /**
     * @return InterpolationStartToken
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param InterpolationStartToken $start
     */
    public function setStart(InterpolationStartToken $start)
    {
        $this->start = $start;
    }
}
