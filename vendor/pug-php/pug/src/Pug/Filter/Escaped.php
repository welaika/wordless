<?php

namespace Pug\Filter;

use Pug\AbstractFilter as FilterBase;

class Escaped extends FilterBase
{
    public function parse($code)
    {
        return htmlentities($code);
    }
}
