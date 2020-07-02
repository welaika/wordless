<?php

namespace Phug\Formatter\Format;

use Phug\Formatter;

class HtmlFormat extends XhtmlFormat
{
    const DOCTYPE = '<!DOCTYPE html>';
    const SELF_CLOSING_TAG = '<%s>';
    const EXPLICIT_CLOSING_TAG = '<%s/>';
    const BOOLEAN_ATTRIBUTE_PATTERN = ' %s';

    public function __construct(Formatter $formatter = null)
    {
        parent::__construct($formatter);

        $this->addPattern('explicit_closing_tag', static::EXPLICIT_CLOSING_TAG);
    }
}
