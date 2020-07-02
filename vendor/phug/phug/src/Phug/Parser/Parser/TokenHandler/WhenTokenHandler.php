<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\WhenToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\WhenNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class WhenTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof WhenToken)) {
            throw new \RuntimeException(
                'You can only pass when tokens to this token handler'
            );
        }

        /** @var WhenNode $node */
        $node = $state->createNode(WhenNode::class, $token);
        $node->setSubject($token->getSubject());
        $node->setName($token->getName());
        $state->setCurrentNode($node);
    }
}
