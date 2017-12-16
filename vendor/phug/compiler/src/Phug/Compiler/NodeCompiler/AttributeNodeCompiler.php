<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\Element\AttributeElement;
use Phug\Formatter\Element\ExpressionElement;
use Phug\Formatter\Element\TextElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\AttributeNode;
use Phug\Parser\NodeInterface;

class AttributeNodeCompiler extends AbstractNodeCompiler
{
    protected function compileName(AttributeNode $node)
    {
        $name = $node->getName();

        if ($node->hasStaticMember('name')) {
            return strval(eval('return '.$name.';'));
        }

        return $name;
    }

    protected function compileValue(AttributeNode $node)
    {
        $value = $node->getValue();

        if ($node->hasStaticValue()) {
            // eval is safe here since pass to it only one valid number or constant string.
            $value = strval(eval('return '.$value.';'));
            $value = new TextElement($value, $node);
            $value->setIsEscaped($node->isEscaped());

            return $value;
        }

        if (is_null($value)) {
            $value = 'true';
        }

        if (is_string($value)) {
            $value = new ExpressionElement($value, $node);
        }

        $this->getCompiler()->assert(
            $value instanceof ExpressionElement,
            'Attribute value can only be a string, a boolean or an expression, '.
            get_class($value).' given.',
            $node
        );

        $value->setIsEscaped($node->isEscaped());
        $value->setIsChecked($node->isChecked());

        return $value;
    }

    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $this->getCompiler()->assert(
            $node instanceof AttributeNode,
            'Unexpected '.get_class($node).' given to attribute compiler.',
            $node
        );

        /**
         * @var AttributeNode $node
         */
        $name = $this->compileName($node);
        $value = $this->compileValue($node);
        $attribute = new AttributeElement($name, $value, $node);
        $attribute->setIsVariadic($node->isVariadic());

        return $attribute;
    }
}
