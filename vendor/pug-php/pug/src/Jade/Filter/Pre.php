<?php

namespace Jade\Filter;

/**
 * Class Jade\Filter\Pre.
 */
class Pre extends AbstractFilter
{
    protected $tag = 'pre';

    public function parse($contents)
    {
        return htmlspecialchars(trim($contents));
    }
}
