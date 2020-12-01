<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\CodeNode;
use Phug\Parser\Node\CommentNode;
use Phug\Parser\Node\TextNode;
use Phug\Parser\NodeInterface;

class CodeNodeCompiler extends AbstractNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $this->getCompiler()->assert(
            $node instanceof CodeNode,
            'Unexpected '.get_class($node).' given to code compiler.',
            $node
        );

        /**
         * @var CodeNode $codeNode
         */
        $codeNode = $node;

        $children = array_filter($codeNode->getChildren(), function (NodeInterface $node) {
            return !($node instanceof CommentNode);
        });

        $texts = array_filter($children, function (NodeInterface $node) {
            return $node instanceof TextNode;
        });

        $code = $this->getCodeElement($codeNode, $texts, $children, $parent);

        if (!$codeNode->isTransformationAllowed()) {
            $code->preventFromTransformation();
        }

        return $code;
    }

    private function getCodeElement(CodeNode $node, array $texts, array $children, ElementInterface $parent = null)
    {
        if (count($texts) === count($children)) {
            return new CodeElement($this->getTextChildren($node), $node);
        }

        $code = new CodeElement(null, $node);

        if ($children[0] instanceof TextNode) {
            $code->setValue($children[0]->getValue());
            $children = array_slice($children, 1);
        }

        $code->setChildren($this->getCompiledNodeList($children, $parent));

        return $code;
    }
}
