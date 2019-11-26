<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\MixinCallToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\ExpressionNode;
use Phug\Parser\Node\MixinCallNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class MixinCallTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof MixinCallToken)) {
            throw new \RuntimeException(
                'You can only pass mixin call tokens to this token handler'
            );
        }

        /** @var MixinCallNode $node */
        $node = $state->createNode(MixinCallNode::class, $token);
        $name = $token->getName();
        if (preg_match('/^#\\{(.+)\\}$/', $name, $match)) {
            /** @var ExpressionNode $name */
            $name = $state->createNode(ExpressionNode::class);
            $name->setValue($match[1]);
            $name->setIsChecked(false);
            $name->setIsEscaped(false);
        }
        $node->setName($name);
        $state->setCurrentNode($node);
    }
}
