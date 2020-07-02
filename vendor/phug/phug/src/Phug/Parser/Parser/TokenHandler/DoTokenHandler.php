<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\DoToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\DoNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class DoTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof DoToken)) {
            throw new \RuntimeException(
                'You can only pass do tokens to this token handler'
            );
        }

        $node = $state->createNode(DoNode::class, $token);
        $state->setCurrentNode($node);
    }
}
