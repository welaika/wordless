<?php

namespace Phug\Formatter\Element;

use Phug\Formatter\Partial\TransformableTrait;
use Phug\Util\Partial\CheckTrait;
use Phug\Util\Partial\EscapeTrait;

class ExpressionElement extends AbstractValueElement
{
    use CheckTrait;
    use EscapeTrait;
    use TransformableTrait;

    /**
     * An element or any context representation the expression is linked to.
     *
     * @var mixed
     */
    protected $link;

    /**
     * Link the expression to a meaningful context such as an attribute element.
     *
     * @param mixed $link
     *
     * @var $this
     */
    public function linkTo($link)
    {
        $this->link = $link;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }
}
