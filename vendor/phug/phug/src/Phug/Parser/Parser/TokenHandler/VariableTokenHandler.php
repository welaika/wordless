<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\VariableToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\VariableNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class VariableTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof VariableToken)) {
            throw new \RuntimeException(
                'You can only pass variable tokens to this token handler'
            );
        }

        /** @var VariableNode $node */
        $node = $state->createNode(VariableNode::class, $token);
        $node->setName($token->getName());
        $state->setCurrentNode($node);
    }
}
