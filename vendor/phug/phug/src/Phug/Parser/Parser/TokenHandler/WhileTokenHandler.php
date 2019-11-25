<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\WhileToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\WhileNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class WhileTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof WhileToken)) {
            throw new \RuntimeException(
                'You can only pass while tokens to this token handler'
            );
        }

        /** @var WhileNode $node */
        $node = $state->createNode(WhileNode::class, $token);
        $node->setSubject($token->getSubject());
        $state->setCurrentNode($node);
    }
}
