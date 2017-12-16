<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\Element\AssignmentElement;
use Phug\Formatter\Element\MarkupElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\ElementNode;
use Phug\Parser\Node\ExpressionNode;
use Phug\Parser\NodeInterface;
use SplObjectStorage;

class ElementNodeCompiler extends AbstractNodeCompiler
{
    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $compiler = $this->getCompiler();
        $compiler->assert(
            $node instanceof ElementNode,
            'Unexpected '.get_class($node).' given to element compiler.',
            $node
        );

        /** @var ElementNode $node */
        $name = $node->getName() ?: $compiler->getOption('default_tag');
        if ($name instanceof ExpressionNode) {
            $name = $compiler->compileNode($name);
        }

        $attributes = new SplObjectStorage();
        foreach ($node->getAttributes() as $attribute) {
            $attributes->attach($compiler->compileNode($attribute, $parent));
        }
        $markup = new MarkupElement($name, $node->isAutoClosed(), $attributes, $node);
        foreach ($node->getAssignments() as $assignment) {
            $compiledAssignment = $compiler->compileNode($assignment, $parent);
            if ($compiledAssignment instanceof AssignmentElement) {
                $markup->addAssignment($compiledAssignment);
            }
        }

        $this->compileNodeChildren($node, $markup);

        $outer = $node->getOuterNode();
        if ($outer) {
            $outerMarkup = $compiler->compileNode($outer);
            $outerMarkup->appendChild($markup);

            return $outerMarkup;
        }

        return $markup;
    }
}
