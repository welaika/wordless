<?php

namespace Phug\Lexer\Token;

use Phug\Lexer\AbstractToken;

class TagInterpolationEndToken extends AbstractToken
{
    /**
     * @var TagInterpolationStartToken
     */
    private $start;

    /**
     * @return TagInterpolationStartToken
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param TagInterpolationStartToken $start
     */
    public function setStart(TagInterpolationStartToken $start)
    {
        $this->start = $start;
    }
}
