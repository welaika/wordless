<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\KeywordToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\KeywordNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class KeywordTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof KeywordToken)) {
            throw new \RuntimeException(
                'You can only pass keyword tokens to this token handler'
            );
        }

        /** @var KeywordNode $node */
        $node = $state->createNode(KeywordNode::class, $token);
        $node->setName($token->getName());
        $node->setValue($token->getValue());
        $state->setCurrentNode($node);
    }
}
