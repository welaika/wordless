<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\Element\TextElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\TextNode;
use Phug\Parser\NodeInterface;

class TextNodeCompiler extends AbstractNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $this->getCompiler()->assert(
            $node instanceof TextNode,
            'Unexpected '.get_class($node).' given to text compiler.',
            $node
        );

        /** @var TextNode $node */
        $text = new TextElement($node->getValue(), $node);
        $text->setIsEscaped($node->isEscaped());

        return $text;
    }
}
