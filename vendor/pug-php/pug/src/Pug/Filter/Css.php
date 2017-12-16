<?php

namespace Pug\Filter;

use Pug\AbstractFilter as FilterBase;

class Css extends FilterBase
{
    public function parse($code)
    {
        return '<style type="text/css">' . $code . '</style>';
    }
}
