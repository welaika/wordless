<?php

namespace Pug\Filter;

use Pug\AbstractFilter as FilterBase;

/**
 * Class Pug\Filter\Pre.
 */
class Pre extends FilterBase
{
    protected $tag = 'pre';

    public function parse($contents)
    {
        return htmlspecialchars(trim($contents));
    }
}
