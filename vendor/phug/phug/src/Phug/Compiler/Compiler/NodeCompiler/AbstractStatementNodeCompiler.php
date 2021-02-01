<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\Element\CodeElement;
use Phug\Parser\NodeInterface;
use Phug\Util\BooleanSubjectInterface;
use Phug\Util\TransformableInterface;

abstract class AbstractStatementNodeCompiler extends AbstractNodeCompiler
{
    protected function wrapStatement(NodeInterface $node, $statement, $subject = null, $noChildrenEnd = ' {}')
    {
        if ($subject !== null && $subject !== '') {
            $statement .= $this->getStatementSubject($node, $subject);
        }

        if (!$node->hasChildren()) {
            $statement .= $noChildrenEnd;
        }

        $code = new CodeElement($statement, $node);

        $this->compileNodeChildren($node, $code);

        return $code;
    }

    private function getStatementSubject(NodeInterface $node, $subject)
    {
        $subject = trim($subject);

        if ($node instanceof BooleanSubjectInterface && $node->hasBooleanSubject() && substr($subject, 0, 1) !== '!') {
            $noTransformation = $node instanceof TransformableInterface ? !$node->isTransformationAllowed() : false;
            $subject = $this->getCompiler()->getFormatter()->formatBoolean($subject, false, $noTransformation);
        }

        return ' ('.$subject.')';
    }
}
