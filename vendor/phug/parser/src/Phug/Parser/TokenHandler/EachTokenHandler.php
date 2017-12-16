<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\EachToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\EachNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class EachTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof EachToken)) {
            throw new \RuntimeException(
                'You can only pass each tokens to this token handler'
            );
        }

        /** @var EachNode $node */
        $node = $state->createNode(EachNode::class, $token);
        $node->setSubject($token->getSubject());
        $node->setItem($token->getItem());
        $node->setKey($token->getKey());
        $state->setCurrentNode($node);
    }
}
