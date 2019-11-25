<?php

namespace Phug\Formatter\Element;

use Phug\Ast\NodeInterface;
use Phug\Formatter\AbstractElement;
use Phug\Formatter\AssignmentContainerInterface;
use Phug\Parser\NodeInterface as ParserNode;
use Phug\Util\Partial\AttributeTrait;
use Phug\Util\Partial\NameTrait;

class AssignmentElement extends AbstractElement
{
    use AttributeTrait;
    use NameTrait;

    /**
     * AssignmentElement constructor.
     *
     * @param string                            $name
     * @param \SplObjectStorage|null            $attributes
     * @param AssignmentContainerInterface|null $markup
     * @param ParserNode|null                   $originNode
     * @param NodeInterface|null                $parent
     * @param array|null                        $children
     */
    public function __construct(
        $name,
        \SplObjectStorage $attributes = null,
        AssignmentContainerInterface $container = null,
        ParserNode $originNode = null,
        NodeInterface $parent = null,
        array $children = null
    ) {
        parent::__construct($originNode, $parent, $children);

        $this->setName($name);

        if ($attributes) {
            $this->getAttributes()->addAll($attributes);
        }

        if ($container) {
            $this->setContainer($container);
        }
    }

    /**
     * @var AssignmentContainerInterface
     */
    private $container;

    /**
     * Set markup subject.
     *
     * @param AssignmentContainerInterface $markup
     */
    public function setContainer(AssignmentContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return AssignmentContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Detach the assignment from its markup.
     */
    public function detach()
    {
        return $this->container->removedAssignment($this);
    }
}
