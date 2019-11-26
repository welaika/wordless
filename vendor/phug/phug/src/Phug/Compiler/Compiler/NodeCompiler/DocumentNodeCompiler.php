<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\Element\DocumentElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\DocumentNode;
use Phug\Parser\NodeInterface;

class DocumentNodeCompiler extends AbstractNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $this->getCompiler()->assert(
            $node instanceof DocumentNode,
            'Unexpected '.get_class($node).' given to document compiler.',
            $node
        );

        $document = new DocumentElement($node);

        $this->compileNodeChildren($node, $document);

        return $document;
    }
}
