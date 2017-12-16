<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\CaseNode;
use Phug\Parser\NodeInterface;

class CaseNodeCompiler extends AbstractStatementNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $this->getCompiler()->assert(
            $node instanceof CaseNode,
            'Unexpected '.get_class($node).' given to case compiler.',
            $node
        );

        /**
         * @var CaseNode $node
         */
        $subject = $node->getSubject();

        return $this->wrapStatement($node, 'switch', $subject);
    }
}
