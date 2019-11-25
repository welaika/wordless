<?php

namespace Phug\Renderer\Profiler;

use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\Token\AttributeStartToken;
use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\Token\NewLineToken;
use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\TokenInterface;

class TokenDump
{
    private $name;
    private $symbol;

    public function __construct(TokenInterface $token)
    {
        $tokenClass = get_class($token);
        $symbol = null;
        $name = null;
        if ($tokenClass === NewLineToken::class) {
            $symbol = '↩';
            $name = 'new line';
        } elseif ($tokenClass === IndentToken::class) {
            $symbol = '→';
            $name = 'indent';
        } elseif ($tokenClass === OutdentToken::class) {
            $symbol = '←';
            $name = 'outdent';
        } elseif ($tokenClass === AttributeStartToken::class) {
            $symbol = '(';
            $name = 'attributes start';
        } elseif ($tokenClass === AttributeEndToken::class) {
            $symbol = ')';
            $name = 'attributes end';
        }

        $this->name = $name;
        $this->symbol = $symbol;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }
}
