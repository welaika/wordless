<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\DoNode;
use Phug\Parser\Node\WhileNode;
use Phug\Parser\NodeInterface;

class WhileNodeCompiler extends AbstractStatementNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $compiler = $this->getCompiler();
        $compiler->assert(
            $node instanceof WhileNode,
            'Unexpected '.get_class($node).' given to while compiler.',
            $node
        );

        /**
         * @var WhileNode $node
         */
        $subject = $node->getSubject();
        $linkedToDoStatement = $node->getPreviousSibling() instanceof DoNode;
        $compiler->assert(
            !($linkedToDoStatement && $node->hasChildren()),
            'While statement cannot have children and come after a do statement.',
            $node
        );
        $whileEnd = $linkedToDoStatement ? ';' : ' {}';

        return $this->wrapStatement($node, 'while', $subject, $whileEnd);
    }
}
