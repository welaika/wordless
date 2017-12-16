<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\ConditionalToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\ConditionalNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class ConditionalTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof ConditionalToken)) {
            throw new \RuntimeException(
                'You can only pass conditional tokens to this token handler'
            );
        }

        /** @var ConditionalNode $node */
        $node = $state->createNode(ConditionalNode::class, $token);
        $node->setSubject($token->getSubject());
        $node->setName($token->getName());
        $state->setCurrentNode($node);
    }
}
