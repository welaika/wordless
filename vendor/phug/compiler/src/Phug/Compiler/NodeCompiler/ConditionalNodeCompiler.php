<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\ConditionalNode;
use Phug\Parser\NodeInterface;

class ConditionalNodeCompiler extends AbstractStatementNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $this->getCompiler()->assert(
            $node instanceof ConditionalNode,
            'Unexpected '.get_class($node).' given to conditional compiler.',
            $node
        );

        /**
         * @var ConditionalNode $node
         */
        $subject = $node->getSubject();
        $name = $node->getName();
        if ($name === 'unless') {
            $name = 'if';
            $subject = '!('.$subject.')';
        }

        return $this->wrapStatement($node, $name, $subject)->check();
    }
}
