<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\CommentToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\CommentNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class CommentTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof CommentToken)) {
            throw new \RuntimeException(
                'You can only pass comment tokens to this token handler'
            );
        }

        /** @var CommentNode $node */
        $node = $state->createNode(CommentNode::class, $token);
        $node->setIsVisible($token->isVisible());
        $state->setCurrentNode($node);
    }
}
