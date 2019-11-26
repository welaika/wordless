<?php

namespace Phug\Compiler\Util;

use Phug\Parser\NodeInterface;

trait YieldHandlerTrait
{
    /**
     * @var NodeInterface
     */
    private $importNode;

    /**
     * @var NodeInterface
     */
    private $yieldNode;

    /**
     * @var bool
     */
    private $importNodeYielded;

    /**
     * @param NodeInterface $importNode
     *
     * @return $this
     */
    public function setImportNode(NodeInterface $importNode)
    {
        $this->importNode = $importNode;
        $this->importNodeYielded = false;

        return $this;
    }

    /**
     * @param NodeInterface $yieldNode
     *
     * @return $this
     */
    public function setYieldNode(NodeInterface $yieldNode)
    {
        $this->yieldNode = $yieldNode;

        return $this;
    }

    /**
     * @return $this
     */
    public function unsetYieldNode()
    {
        $this->yieldNode = null;

        return $this;
    }

    /**
     * @return NodeInterface
     */
    public function getYieldNode()
    {
        return $this->yieldNode;
    }

    /**
     * @return bool
     */
    public function isImportNodeYielded()
    {
        return (bool) $this->importNodeYielded;
    }

    /**
     * @return NodeInterface
     */
    public function getImportNode()
    {
        $this->importNodeYielded = true;

        return $this->importNode;
    }
}
