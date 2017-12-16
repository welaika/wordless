<?php

namespace Phug\Compiler\Event;

use Phug\CompilerEvent;
use Phug\Event;
use Phug\Parser\NodeInterface;

class NodeEvent extends Event
{
    private $node;

    /**
     * NodeEvent constructor.
     *
     * @param NodeInterface $node
     */
    public function __construct(NodeInterface $node)
    {
        parent::__construct(CompilerEvent::NODE);

        $this->node = $node;
    }

    /**
     * @return NodeInterface
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @param NodeInterface $node
     */
    public function setNode($node)
    {
        $this->node = $node;
    }
}
