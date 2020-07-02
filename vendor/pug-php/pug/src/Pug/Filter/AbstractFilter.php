<?php

namespace Pug\Filter;

use Pug\Compiler;
use Pug\Nodes\Filter;

/**
 * Class Pug\Filter\AbstractFilter.
 */
abstract class AbstractFilter extends AbstractFilterBase implements FilterInterface
{
    /**
     * @obsolete
     */
    protected function getNodeString()
    {
        throw new \RuntimeException('->getNodeString is no longer supported since you get now contents as a string.');
    }

    public function __invoke(Filter $node, Compiler $compiler)
    {
        throw new \RuntimeException('Pug\Filter\FilterInterface is no longer supported. Now use Pug\FilterInterface instead.');
    }
}
