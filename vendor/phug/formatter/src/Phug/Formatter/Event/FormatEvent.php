<?php

namespace Phug\Formatter\Event;

use Phug\Event;
use Phug\Formatter\ElementInterface;
use Phug\Formatter\FormatInterface;
use Phug\FormatterEvent;

class FormatEvent extends Event
{
    private $element;
    private $format;

    /**
     * FormatEvent constructor.
     *
     * @param ElementInterface $element
     * @param FormatInterface  $format
     */
    public function __construct(ElementInterface $element, FormatInterface $format)
    {
        parent::__construct(FormatterEvent::FORMAT);

        $this->element = $element;
        $this->format = $format;
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
    public function setElement(ElementInterface $element = null)
    {
        $this->element = $element;
    }

    /**
     * @return FormatInterface
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param FormatInterface $format
     */
    public function setFormat(FormatInterface $format)
    {
        $this->format = $format;
    }
}
