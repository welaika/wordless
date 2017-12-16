<?php

namespace Pug\Filter;

use Pug\AbstractFilter as FilterBase;

/**
 * @obsolete
 * Already included in Phug.
 */
class Cdata extends FilterBase
{
    public function parse($code)
    {
        return "<![CDATA[\n$code\n]]>";
    }
}
