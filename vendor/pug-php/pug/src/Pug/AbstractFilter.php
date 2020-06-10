<?php

namespace Pug;

use Pug\Filter\AbstractFilterBase;

/**
 * Class Pug\AbstractFilter.
 */
abstract class AbstractFilter extends AbstractFilterBase implements FilterInterface
{
    public function __invoke($code, array $options = null)
    {
        return $this->pugInvoke($code, $options);
    }
}
