<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\YieldToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\YieldNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class YieldTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof YieldToken)) {
            throw new \RuntimeException(
                'You can only pass yield tokens to this token handler'
            );
        }

        $state->setCurrentNode($state->createNode(YieldNode::class, $token));
    }
}
