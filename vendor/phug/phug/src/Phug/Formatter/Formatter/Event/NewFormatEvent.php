<?php

namespace Phug\Formatter\Event;

use Phug\Event;
use Phug\Formatter;
use Phug\Formatter\FormatInterface;
use Phug\FormatterEvent;

class NewFormatEvent extends Event
{
    private $formatter;
    private $format;

    /**
     * FormatEvent constructor.
     *
     * @param Formatter       $formatter
     * @param FormatInterface $format
     */
    public function __construct(Formatter $formatter, FormatInterface $format)
    {
        parent::__construct(FormatterEvent::NEW_FORMAT);

        $this->formatter = $formatter;
        $this->format = $format;
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
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @return mixed
     */
    public function getFormatter()
    {
        return $this->formatter;
    }
}
