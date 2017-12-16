<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\Element\AssignmentElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\AssignmentNode;
use Phug\Parser\Node\AttributeNode;
use Phug\Parser\NodeInterface;
use SplObjectStorage;

class AssignmentNodeCompiler extends AbstractNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $this->getCompiler()->assert(
            $node instanceof AssignmentNode,
            'Unexpected '.get_class($node).' given to assignment compiler.',
            $node
        );

        /**
         * @var AssignmentNode $node
         */
        $name = $node->getName();
        $attributes = new SplObjectStorage();
        $input = iterator_to_array($node->getAttributes());
        array_walk($input, function (AttributeNode $attribute) use ($attributes, $parent) {
            $attributes->attach($this->getCompiler()->compileNode($attribute, $parent));
        });

        return new AssignmentElement($name, $attributes, null, $node);
    }
}
