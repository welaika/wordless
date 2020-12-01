<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\AttributeListNode;
use Phug\Parser\NodeInterface;

class AttributeListNodeCompiler extends AbstractNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $this->getCompiler()->assert(
            $node instanceof AttributeListNode,
            'Unexpected '.get_class($node).' given to attribute list compiler.',
            $node
        );

        return null;
    }
}
