<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\DoctypeToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\DoctypeNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class DoctypeTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof DoctypeToken)) {
            throw new \RuntimeException(
                'You can only pass doctype tokens to this token handler'
            );
        }

        /** @var DoctypeNode $node */
        $node = $state->createNode(DoctypeNode::class, $token);
        $node->setName($token->getName());
        $state->setCurrentNode($node);
    }
}
