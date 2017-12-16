<?php

namespace Pug\Filter;

use Pug\AbstractFilter as FilterBase;

/**
 * Class Pug\Filter\Javascript.
 */
class Javascript extends FilterBase
{
    public function parse($code)
    {
        return '<script type="text/javascript">' . $code . '</script>';
    }
}
