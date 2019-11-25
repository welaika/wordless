<?php

namespace Phug\Compiler\Event;

use Phug\CompilerEvent;
use Phug\Event;
use Phug\Formatter\ElementInterface;

class ElementEvent extends Event
{
    private $element;
    private $nodeEvent;

    /**
     * ElementEvent constructor.
     *
     * @param ElementInterface $element
     */
    public function __construct(NodeEvent $nodeEvent, ElementInterface $element)
    {
        parent::__construct(CompilerEvent::ELEMENT);

        $this->nodeEvent = $nodeEvent;
        $this->element = $element;
    }

    /**
     * @return NodeEvent
     */
    public function getNodeEvent()
    {
        return $this->nodeEvent;
    }

    /**
     * @return ElementInterface
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @param ElementInterface $element
     */
    public function setElement($element)
    {
        $this->element = $element;
    }
}
