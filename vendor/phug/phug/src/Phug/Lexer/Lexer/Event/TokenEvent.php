<?php

namespace Phug\Lexer\Event;

use Phug\Event;
use Phug\Lexer\TokenInterface;
use Phug\LexerEvent;

class TokenEvent extends Event
{
    private $token;
    private $tokenGenerator = null;

    public function __construct(TokenInterface $token)
    {
        parent::__construct(LexerEvent::TOKEN);

        $this->token = $token;
    }

    /**
     * @return TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    public function setToken(TokenInterface $token)
    {
        $this->token = $token;
    }

    public function getTokenGenerator()
    {
        return $this->tokenGenerator;
    }

    public function setTokenGenerator(\Iterator $tokens)
    {
        $this->tokenGenerator = $tokens;
    }
}
