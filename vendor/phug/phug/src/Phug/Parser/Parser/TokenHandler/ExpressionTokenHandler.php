<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\ExpressionNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class ExpressionTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof ExpressionToken)) {
            throw new \RuntimeException(
                'You can only pass expression tokens to this token handler'
            );
        }

        /** @var ExpressionNode $node */
        $node = $state->createNode(ExpressionNode::class, $token);
        $node->setIsEscaped($token->isEscaped());
        $node->setIsChecked($token->isChecked());
        $node->setValue($token->getValue());

        $state->append($node);
    }
}
