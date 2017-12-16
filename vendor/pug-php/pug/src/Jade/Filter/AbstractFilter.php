<?php

namespace Jade\Filter;

abstract class AbstractFilter
{
    public function __construct()
    {
        throw new \InvalidArgumentException(
            'Jade namespace is no longer available, use Pug instead.'
        );
    }
}
