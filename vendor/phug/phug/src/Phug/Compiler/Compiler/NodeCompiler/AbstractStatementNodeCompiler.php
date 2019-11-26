<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\Element\CodeElement;
use Phug\Parser\NodeInterface;

abstract class AbstractStatementNodeCompiler extends AbstractNodeCompiler
{
    protected function wrapStatement(NodeInterface $node, $statement, $subject = null, $noChildrenEnd = ' {}')
    {
        if ($subject !== null && $subject !== '') {
            $statement .= ' ('.$subject.')';
        }

        if (!$node->hasChildren()) {
            $statement .= $noChildrenEnd;
        }

        $code = new CodeElement($statement, $node);

        $this->compileNodeChildren($node, $code);

        return $code;
    }
}
