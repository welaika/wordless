<?php

namespace Phug\Compiler\Element;

use Phug\Ast\NodeInterface;
use Phug\CompilerInterface;
use Phug\Formatter\AbstractElement;
use Phug\Parser\NodeInterface as ParserNode;

class BlockElement extends AbstractElement
{
    /**
     * @var array[CompilerInterface]
     */
    protected $compilers;

    /**
     * @var string
     */
    protected $name;

    public function __construct(
        CompilerInterface $compiler,
        $name = '',
        ParserNode $originNode = null,
        NodeInterface $parent = null,
        array $children = null
    ) {
        $blocks = &$compiler->getBlocksByName($name);
        $blocks[] = $this;
        $this->compilers = [$compiler];
        $this->name = $name;

        parent::__construct($originNode, $parent, $children);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Link another compiler.
     *
     * @param CompilerInterface $compiler
     *
     * @return $this
     */
    public function addCompiler(CompilerInterface $compiler)
    {
        if (!in_array($compiler, $this->compilers)) {
            $blocks = &$compiler->getBlocksByName($this->name);
            $blocks[] = $this;
            $this->compilers[] = $compiler;
        }

        return $this;
    }

    public function proceedChildren(array $newChildren, $mode)
    {
        $offset = 0;
        $length = 0;
        $children = $this->getChildren();

        if ($mode === 'replace') {
            $length = count($children);
        } elseif ($mode === 'append') {
            $offset = count($children);
        }

        array_splice($children, $offset, $length, $newChildren);

        return $this->setChildren($children);
    }

    public function __clone()
    {
        parent::__clone();

        foreach ($this->compilers as $compiler) {
            $blocks = &$compiler->getBlocksByName($this->name);
            $blocks[] = $this;
        }
    }
}
