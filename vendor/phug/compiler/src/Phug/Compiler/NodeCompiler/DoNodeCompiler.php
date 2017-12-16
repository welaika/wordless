<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\DoNode;
use Phug\Parser\NodeInterface;

class DoNodeCompiler extends AbstractStatementNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $this->getCompiler()->assert(
            $node instanceof DoNode,
            'Unexpected '.get_class($node).' given to do compiler.',
            $node
        );

        return $this->wrapStatement($node, 'do');
    }
}
