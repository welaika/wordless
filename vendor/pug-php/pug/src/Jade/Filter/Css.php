<?php

namespace Jade\Filter;

use Jade\Compiler;
use Jade\Nodes\Filter;

class Css extends AbstractFilter
{
    public function __invoke(Filter $node, Compiler $compiler)
    {
        return '<style type="text/css">' . $this->getNodeString($node, $compiler) . '</style>';
    }
}
