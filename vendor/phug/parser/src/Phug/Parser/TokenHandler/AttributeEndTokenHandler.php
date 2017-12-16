<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\AttributeEndToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class AttributeEndTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof AttributeEndToken)) {
            throw new \RuntimeException(
                'You can only pass attribute end tokens to this token handler'
            );
        }
    }
}
