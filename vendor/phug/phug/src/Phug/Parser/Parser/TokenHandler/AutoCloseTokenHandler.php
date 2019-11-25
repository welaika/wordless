<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\AutoCloseToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class AutoCloseTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof AutoCloseToken)) {
            throw new \RuntimeException(
                'You can only pass auto-close tokens to this token handler'
            );
        }

        if (!$state->currentNodeIs([ElementNode::class])) {
            $state->throwException(
                'Auto-close operators can only be used on elements',
                0,
                $token
            );
        }

        $state->getCurrentNode()->autoClose();
    }
}
