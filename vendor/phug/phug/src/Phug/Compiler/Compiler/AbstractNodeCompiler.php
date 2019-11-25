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

    private function compileParserNode(NodeInterface $node, ElementInterface $element = null)
    {
        return $node instanceof ParserNodeInterface
            ? $this->getCompiler()->compileNode($node, $element)
            : null;
    }

    public function __construct(CompilerInterface $compiler)
    {
        $this->compiler = $compiler;
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

    /**
     * @return CompilerInterface
     */
    public function getCompiler()
    {
        return $this->compiler;
    }

    public function getCompiledNodeList($nodeList, ElementInterface $element = null)
    {
        return array_values(array_filter(array_map(
            function (NodeInterface $childNode) use ($element) {
                return $this->compileParserNode($childNode, $element);
            },
            array_filter($nodeList)
        )));
    }

    public function getCompiledChildren(NodeInterface $node, ElementInterface $element = null)
    {
        return $this->getCompiledNodeList($node->getChildren(), $element);
    }

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

    public function createVariable($node, $name, $value)
    {
        $variable = new CodeElement('$'.$name, $node);

        return new VariableElement($variable, $value, $node);
    }
}
