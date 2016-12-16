<?php

namespace Jade\Filter;

use Jade\Compiler;
use Jade\Nodes\Filter;

class Escaped extends AbstractFilter
{
    public function __invoke(Filter $node, Compiler $compiler)
    {
        return htmlentities($this->getNodeString($node, $compiler));
    }
}
