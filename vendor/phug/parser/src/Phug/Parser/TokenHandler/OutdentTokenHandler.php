<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\OutdentToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class OutdentTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof OutdentToken)) {
            throw new \RuntimeException(
                'You can only pass outdent tokens to this token handler'
            );
        }

        $state->leave();
    }
}
