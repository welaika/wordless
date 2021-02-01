<?php

namespace Phug\Compiler;

use Phug\Ast\NodeInterface;
use Phug\CompilerInterface;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\Element\VariableElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\CommentNode;
use Phug\Parser\Node\TextNode;
use Phug\Parser\NodeInterface as ParserNodeInterface;

abstract class AbstractNodeCompiler implements NodeCompilerInterface
{
    /**
     * @var CompilerInterface
     */
    private $compiler;

    public function __construct(CompilerInterface $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * Get the master compiler.
     *
     * @return CompilerInterface
     */
    public function getCompiler()
    {
        return $this->compiler;
    }

    /**
     * Compile each node of a given list and return an array of elements.
     *
     * @param NodeInterface[]       $nodeList
     * @param ElementInterface|null $element
     *
     * @return ElementInterface[]
     */
    public function getCompiledNodeList($nodeList, ElementInterface $element = null)
    {
        return array_values(array_filter(array_map(
            function (NodeInterface $childNode) use ($element) {
                return $this->compileParserNode($childNode, $element);
            },
            array_filter($nodeList)
        )));
    }

    /**
     * Compile each child of a given node and return an array of elements.
     *
     * @param NodeInterface         $node
     * @param ElementInterface|null $element
     *
     * @return ElementInterface[]
     */
    public function getCompiledChildren(NodeInterface $node, ElementInterface $element = null)
    {
        return $this->getCompiledNodeList($node->getChildren(), $element);
    }

    /**
     * Compile each child of a given node and append each compiled element into the
     * given parent element.
     *
     * @param NodeInterface         $node
     * @param ElementInterface|null $element
     */
    public function compileNodeChildren(NodeInterface $node, ElementInterface $element = null)
    {
        $children = array_filter($node->getChildren());
        array_walk($children, function (NodeInterface $childNode) use ($element) {
            $childElement = $this->compileParserNode($childNode, $element);
            if ($childElement) {
                $element->appendChild($childElement);
            }
        });
    }

    /**
     * Create a variable element from a given node with a given name and expression.
     *
     * @param \Phug\Parser\NodeInterface                $node
     * @param string                                    $name
     * @param \Phug\Formatter\Element\ExpressionElement $value
     *
     * @return VariableElement
     */
    public function createVariable($node, $name, $value)
    {
        $variable = new CodeElement('$'.$name, $node);

        return new VariableElement($variable, $value, $node);
    }

    protected function getTextChildren(ParserNodeInterface $node)
    {
        $children = array_filter($node->getChildren(), function (ParserNodeInterface $node) {
            return !($node instanceof CommentNode);
        });

        return implode("\n", array_map(function (TextNode $text) {
            return $text->getValue();
        }, $children));
    }

    private function compileParserNode(NodeInterface $node, ElementInterface $element = null)
    {
        return $node instanceof ParserNodeInterface
            ? $this->getCompiler()->compileNode($node, $element)
            : null;
    }
}
