<?php

namespace Pug\Filter;

/**
 * Class Pug\Filter\AbstractFilterBase.
 */
abstract class AbstractFilterBase
{
    use WrapTagTrait;

    public function pugInvoke($code, array $options = null)
    {
        if (method_exists($this, 'parse')) {
            $code = $this->parse($code, $options);
        }

        return $this->wrapInTag($code);
    }
}
