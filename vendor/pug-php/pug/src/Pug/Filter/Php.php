<?php

namespace Pug\Filter;

use Pug\FilterInterface as FilterBase;

/**
 * Class Pug\Filter\Php.
 */
class Php implements FilterBase
{
    public function __invoke($code, array $options = null)
    {
        return "<?php\n$code\n?>";
    }
}
