<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\IndentToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class IndentTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof IndentToken)) {
            throw new \RuntimeException(
                'You can only pass indent tokens to this token handler'
            );
        }

        $state->enter();
    }
}
