<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\BlockToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\BlockNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class BlockTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof BlockToken)) {
            throw new \RuntimeException(
                'You can only pass block tokens to this token handler'
            );
        }

        /** @var BlockNode $node */
        $node = $state->createNode(BlockNode::class, $token);
        $node->setName($token->getName());
        $node->setMode($token->getMode());
        $state->setCurrentNode($node);
    }
}
