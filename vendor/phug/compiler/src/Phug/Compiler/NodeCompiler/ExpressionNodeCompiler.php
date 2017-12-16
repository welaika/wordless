<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\ExpressionNode;
use Phug\Parser\NodeInterface;

class ExpressionNodeCompiler extends AbstractNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $this->getCompiler()->assert(
            $node instanceof ExpressionNode,
            'Unexpected '.get_class($node).' given to expression compiler.',
            $node
        );

        /** @var ExpressionNode $node */
        $value = $node->getValue();
        $expression = new ExpressionElement($value, $node);
        $expression->setIsChecked($node->isChecked());
        $expression->setIsEscaped($node->isEscaped());

        return $expression;
    }
}
