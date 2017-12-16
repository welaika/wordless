<?php

namespace Pug\Filter;

use Pug\Compiler;
use Pug\Nodes\Filter;

/**
 * Class Pug\Filter\AbstractFilter.
 */
abstract class AbstractFilter implements FilterInterface
{
    use WrapTagTrait;

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

    public function pugInvoke($code, array $options = null)
    {
        if (method_exists($this, 'parse')) {
            $code = $this->parse($code, $options);
        }

        return $this->wrapInTag($code);
    }
}
