<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\ForToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\ForNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class ForTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof ForToken)) {
            throw new \RuntimeException(
                'You can only pass for tokens to this token handler'
            );
        }

        /** @var ForNode $node */
        $node = $state->createNode(ForNode::class, $token);
        $node->setSubject($token->getSubject());
        $state->setCurrentNode($node);
    }
}
