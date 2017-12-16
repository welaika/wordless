<?php

namespace Pug\Filter;

use Pug\Compiler;
use Pug\Nodes\Filter;

/**
 * Interface Pug\Filter\FilterInterface.
 */
interface FilterInterface
{
    public function __invoke(Filter $filter, Compiler $compiler);
}
