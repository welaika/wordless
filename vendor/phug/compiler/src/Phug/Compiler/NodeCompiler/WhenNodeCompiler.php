<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\WhenNode;
use Phug\Parser\NodeInterface;

class WhenNodeCompiler extends AbstractNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $this->getCompiler()->assert(
            $node instanceof WhenNode,
            'Unexpected '.get_class($node).' given to when compiler.',
            $node
        );

        /**
         * @var WhenNode $node
         */
        $value = $node->getSubject();

        if ($value === null) {
            $parent->appendChild(new CodeElement('default:', $node));
            $this->compileNodeChildren($node, $parent);

            return null;
        }

        $parent->appendChild(new CodeElement('case '.$value.':', $node));
        if ($node->hasChildren()) {
            $this->compileNodeChildren($node, $parent);
            $parent->appendChild(new CodeElement('break;', $node));
        }

        return null;
    }
}
