<?php

namespace Jade\Filter;

use Jade\Compiler;
use Jade\Nodes\Filter;

class FooBar extends AbstractFilter
{
    /**
     * @param Filter   $node
     * @param Compiler $compiler
     *
     * @return string
     */
    public function __invoke(Filter $node, Compiler $compiler)
    {
        return strtr(strtoupper($this->getNodeString($node, $compiler)), array(
            '(' => ')',
            'SMALL' => 'TALL',
        ));
    }

}
