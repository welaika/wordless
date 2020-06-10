<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\Element\DoctypeElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\DoctypeNode;
use Phug\Parser\NodeInterface;

class DoctypeNodeCompiler extends AbstractNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $compiler = $this->getCompiler();
        $compiler->assert(
            $node instanceof DoctypeNode,
            'Unexpected '.get_class($node).' given to doctype compiler.',
            $node
        );

        /** @var DoctypeNode $node */
        $name = $node->getName() ?: $compiler->getOption('default_doctype');

        return new DoctypeElement($name, $node);
    }
}
