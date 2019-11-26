<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\CaseToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\CaseNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class CaseTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof CaseToken)) {
            throw new \RuntimeException(
                'You can only pass case tokens to this token handler'
            );
        }

        /** @var CaseNode $node */
        $node = $state->createNode(CaseNode::class, $token);
        $node->setSubject($token->getSubject());
        $state->setCurrentNode($node);
    }
}
