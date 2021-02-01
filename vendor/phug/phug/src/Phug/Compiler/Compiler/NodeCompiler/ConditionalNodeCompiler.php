<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\ConditionalNode;
use Phug\Parser\NodeInterface;
use Phug\Util\TransformableInterface;

class ConditionalNodeCompiler extends AbstractStatementNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $compiler = $this->getCompiler();

        $compiler->assert(
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
            $noTransformation = $node instanceof TransformableInterface ? !$node->isTransformationAllowed() : false;
            $subject = '!('.$compiler->getFormatter()->formatBoolean($subject, false, $noTransformation).')';
        }

        return $this->wrapStatement($node, $name, $subject)->check();
    }
}
