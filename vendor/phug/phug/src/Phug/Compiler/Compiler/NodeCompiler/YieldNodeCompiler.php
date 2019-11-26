<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\YieldNode;
use Phug\Parser\NodeInterface;

class YieldNodeCompiler extends AbstractNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $compiler = $this->getCompiler();
        $compiler->assert(
            $node instanceof YieldNode,
            'Unexpected '.get_class($node).' given to yield compiler.',
            $node
        );

        if ($node !== $compiler->getYieldNode()) {
            $importNode = $compiler->getImportNode();
            if ($importNode && $importNode->hasChildren()) {
                $compiler->setYieldNode($node);
                $this->compileNodeChildren($importNode, $parent);
                $compiler->unsetYieldNode();
            }
        }

        return new ExpressionElement('""', $node, $parent);
    }
}
