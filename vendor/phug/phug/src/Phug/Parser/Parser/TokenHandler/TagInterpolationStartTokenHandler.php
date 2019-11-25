<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\TagInterpolationStartToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\CodeNode;
use Phug\Parser\Node\ExpressionNode;
use Phug\Parser\Node\TextNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class TagInterpolationStartTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof TagInterpolationStartToken)) {
            throw new \RuntimeException(
                'You can only pass tag interpolation start tokens to this token handler'
            );
        }

        $node = $state->getCurrentNode();
        if ($state->currentNodeIs([
            TextNode::class,
            CodeNode::class,
            ExpressionNode::class,
        ])) {
            $node = $node->getParent();
        }
        if ($node) {
            $state->pushInterpolationNode($node);
        }
        $state->getInterpolationStack()->attach($token->getEnd(), (object) [
            'currentNode' => $node,
            'parentNode'  => $state->getParentNode(),
        ]);
        $state->store();
    }
}
