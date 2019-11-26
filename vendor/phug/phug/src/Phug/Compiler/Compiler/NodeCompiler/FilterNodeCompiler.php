<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\Compiler\AbstractNodeCompiler;
use Phug\Formatter\Element\TextElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\FilterNode;
use Phug\Parser\Node\TextNode;
use Phug\Parser\NodeInterface;

class FilterNodeCompiler extends AbstractNodeCompiler
{
    protected function compileText($name, $children, $parent, $indentLevel)
    {
        return implode("\n", array_map(function (TextNode $node) use ($name, $indentLevel, $parent) {
            $compiler = $this->getCompiler();
            $element = $compiler->compileNode($node, $parent);
            $compiler->assert(
                $element instanceof TextElement,
                'Unexpected '.get_class($element).' in '.$name.' filter.',
                $node
            );
            /** @var TextElement $element */
            $text = $element->getValue();
            if ($node->hasChildren()) {
                $childrenIndent = $indentLevel + 1;
                $text .=
                    "\n".
                    str_repeat(' ', $childrenIndent * 2).
                    $this->compileText(
                        $name,
                        $node->getChildren(),
                        $parent,
                        $childrenIndent
                    );
            }

            return $text;
        }, $children));
    }

    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $compiler = $this->getCompiler();
        $compiler->assert(
            $node instanceof FilterNode,
            'Unexpected '.get_class($node).' given to filter compiler.',
            $node
        );

        /**
         * @var FilterNode $node
         */
        if ($node->getImport()) {
            return null;
        }

        $name = $node->getName();

        $text = $this->compileText($name, $node->getChildren(), $parent, 0);
        $names = explode(':', $name);

        while ($name = array_pop($names)) {
            $this->getCompiler()->assert(
                $compiler->hasFilter($name),
                'Unknown filter '.$name.'.',
                $node
            );

            $options = [];
            foreach ($node->getAttributes() as $attribute) {
                $__pug_eval_attribute = $attribute->getValue();
                $options[$attribute->getName()] = call_user_func(function () use ($__pug_eval_attribute) {
                    return eval('return '.$__pug_eval_attribute.';');
                });
            }

            $text = $this->proceedFilter(
                $compiler->getFilter($name),
                $text,
                $options
            );
        }

        return new TextElement($text, $node);
    }

    public function proceedFilter($filter, $input, $options)
    {
        if (!is_callable($filter) && class_exists($filter)) {
            $filter = new $filter();
        }

        if (is_object($filter) && method_exists($filter, 'parse')) {
            $filter = [$filter, 'parse'];
        }

        return strval(call_user_func(
            $filter,
            $input,
            $options,
            $this->getCompiler(),
            $this
        ));
    }
}
