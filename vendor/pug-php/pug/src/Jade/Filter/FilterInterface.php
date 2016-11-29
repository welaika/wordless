<?php

namespace Jade\Filter;

use Jade\Compiler;
use Jade\Nodes\Filter;

/**
 * Interface Jade\Filter\FilterInterface.
 */
interface FilterInterface
{
    public function __invoke(Filter $filter, Compiler $compiler);
}
