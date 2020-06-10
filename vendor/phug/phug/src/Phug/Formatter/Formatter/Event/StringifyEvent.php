<?php

namespace Phug\Formatter\Event;

use Phug\Event;
use Phug\Formatter\ElementInterface;
use Phug\Formatter\FormatInterface;
use Phug\FormatterEvent;

class StringifyEvent extends Event
{
    private $formatEvent;
    private $output;

    /**
     * FormatEvent constructor.
     *
     * @param ElementInterface $element
     * @param FormatInterface  $format
     */
    public function __construct(FormatEvent $formatEvent, $output)
    {
        parent::__construct(FormatterEvent::STRINGIFY);

        $this->formatEvent = $formatEvent;
        $this->output = $output;
    }

    /**
     * @return FormatEvent
     */
    public function getFormatEvent()
    {
        return $this->formatEvent;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param string $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }
}
