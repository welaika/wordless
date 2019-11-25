<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\ExpressionNode;
use Phug\Parser\Node\VariableNode;
use Phug\Parser\NodeInterface;

class VariableNodeCompiler extends AbstractNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $compiler = $this->getCompiler();
        $compiler->assert(
            $node instanceof VariableNode,
            'Unexpected '.get_class($node).' given to variable compiler.',
            $node
        );
        /**
         * @var VariableNode $node
         */
        $count = $node->getChildCount();
        $child = $count === 1 ? $node->getChildAt(0) : null;
        $compiler->assert(
            $child instanceof ExpressionNode,
            'Variable should be followed by exactly 1 expression.',
            $node
        );

        return $this->createVariable(
            $node,
            $node->getName(),
            $compiler->compileNode($child, $parent)
        );
    }
}
