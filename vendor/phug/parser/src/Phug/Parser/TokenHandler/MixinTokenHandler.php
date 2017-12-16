<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\MixinToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\MixinNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class MixinTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof MixinToken)) {
            throw new \RuntimeException(
                'You can only pass mixin tokens to this token handler'
            );
        }

        /** @var MixinNode $node */
        $node = $state->createNode(MixinNode::class, $token);
        $node->setName($token->getName());
        $state->setCurrentNode($node);
    }
}
