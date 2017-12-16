<?php

namespace Pug;

use Pug\Filter\WrapTagTrait;

/**
 * Class Pug\AbstractFilter.
 */
abstract class AbstractFilter implements FilterInterface
{
    use WrapTagTrait;

    public function __invoke($code, array $options = null)
    {
        return $this->pugInvoke($code, $options);
    }

    public function pugInvoke($code, array $options = null)
    {
        if (method_exists($this, 'parse')) {
            $code = $this->parse($code, $options);
        }

        return $this->wrapInTag($code);
    }
}
