<?php

namespace Phug\Parser\TokenHandler;

use Phug\Lexer\Token\ExpressionToken;
use Phug\Lexer\Token\InterpolationEndToken;
use Phug\Lexer\Token\InterpolationStartToken;
use Phug\Lexer\TokenInterface;
use Phug\Parser\Node\CodeNode;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\ExpressionNode;
use Phug\Parser\Node\TextNode;
use Phug\Parser\State;
use Phug\Parser\TokenHandlerInterface;

class InterpolationStartTokenHandler implements TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state)
    {
        if (!($token instanceof InterpolationStartToken)) {
            throw new \RuntimeException(
                'You can only pass interpolation start tokens to this token handler'
            );
        }

        $node = $state->getCurrentNode();
        if (!$node) {
            /** @var ElementNode $element */
            $element = $state->createNode(ElementNode::class, $token);
            $state->setCurrentNode($element);

            foreach ($state->lookUpNext([ExpressionToken::class]) as $expression) {
                /** @var ExpressionNode $expressionNode */
                $expressionNode = $state->createNode(ExpressionNode::class, $expression);
                $expressionNode->check();
                $expressionNode->unescape();
                $expressionNode->setValue($expression->getValue());
                $element->setName($expressionNode);
            }

            if (!$state->expect([InterpolationEndToken::class])) {
                $state->throwException(
                    'Interpolation not properly closed',
                    0,
                    $token
                );
            }

            return;
        }
        if ($state->currentNodeIs([
            TextNode::class,
            CodeNode::class,
            ExpressionNode::class,
        ])) {
            $node = $node->getParent();
        }
        $state->getInterpolationStack()->attach($token->getEnd(), (object) [
            'currentNode' => $node,
            'parentNode'  => $state->getParentNode(),
        ]);
        $state->store();
    }
}
