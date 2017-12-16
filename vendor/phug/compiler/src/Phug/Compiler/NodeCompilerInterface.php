<?php

namespace Phug\Compiler;

use Phug\Ast\NodeInterface;
use Phug\Compiler\Element\BlockElement;
use Phug\CompilerInterface;
use Phug\Formatter\ElementInterface;
use Phug\Parser\NodeInterface as ParserNodeInterface;

interface NodeCompilerInterface
{
    /**
     * @param $nodeList
     * @param ElementInterface $parent
     *
     * @return array
     */
    public function getCompiledNodeList($nodeList, ElementInterface $parent = null);

    /**
     * @param NodeInterface    $node
     * @param ElementInterface $parent
     *
     * @return array
     */
    public function getCompiledChildren(NodeInterface $node, ElementInterface $parent = null);

    /**
     * @param NodeInterface         $node
     * @param ElementInterface|null $element
     *
     * @return mixed
     */
    public function compileNodeChildren(NodeInterface $node, ElementInterface $element = null);

    /**
     * @param ParserNodeInterface $node
     * @param ElementInterface    $parent
     *
     * @return null|BlockElement|ElementInterface
     */
    public function compileNode(ParserNodeInterface $node, ElementInterface $parent = null);

    /**
     * @return CompilerInterface
     */
    public function getCompiler();
}
