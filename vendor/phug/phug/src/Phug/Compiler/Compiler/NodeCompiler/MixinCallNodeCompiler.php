<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\Element\AssignmentElement;
use Phug\Formatter\Element\MixinCallElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\AssignmentNode;
use Phug\Parser\Node\AttributeNode;
use Phug\Parser\Node\ExpressionNode;
use Phug\Parser\Node\MixinCallNode;
use Phug\Parser\NodeInterface as ParserNodeInterface;

class MixinCallNodeCompiler extends AbstractNodeCompiler
{
    public function compileNode(ParserNodeInterface $node, ElementInterface $parent = null)
    {
        $compiler = $this->getCompiler();
        $compiler->assert(
            $node instanceof MixinCallNode,
            'Unexpected '.get_class($node).' given to mixin call compiler.',
            $node
        );

        $mixinCall = new MixinCallElement($node, $parent);

        /** @var MixinCallNode $node */
        $name = $node->getName();
        if ($name instanceof ExpressionNode) {
            $name = $compiler->compileNode($name, $mixinCall);
        }
        $mixinCall->setName($name);

        foreach ($node->getAttributes() as $attribute) {
            /* @var AttributeNode $attribute */
            $mixinCall->getAttributes()->attach($compiler->compileNode($attribute, $mixinCall));
        }
        foreach ($node->getAssignments() as $assignment) {
            /* @var AssignmentNode $assignment */
            $compiledAssignment = $compiler->compileNode($assignment, $mixinCall);
            if ($compiledAssignment instanceof AssignmentElement) {
                $mixinCall->addAssignment($compiledAssignment);
            }
        }

        $this->compileNodeChildren($node, $mixinCall);

        $outer = $node->getOuterNode();
        if ($outer) {
            $outerMarkup = $compiler->compileNode($outer);
            $outerMarkup->appendChild($mixinCall);

            return $outerMarkup;
        }

        return $mixinCall;
    }
}
