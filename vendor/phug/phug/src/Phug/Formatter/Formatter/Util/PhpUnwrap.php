<?php

namespace Phug\Formatter\Util;

use Phug\Formatter;

class PhpUnwrap extends PhpUnwrapString
{
    public function __construct($element, Formatter $formatter)
    {
        parent::__construct(implode('', array_map(function ($child) use ($formatter) {
            return is_string($child) ? $child : $formatter->format($child);
        }, is_array($element) ? $element : [$element])));

        $this->unwrapStart();
        $this->unwrapEnd();
    }
}
