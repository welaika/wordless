<?php

namespace Phug\Formatter\Element;

use Phug\Util\Partial\CheckTrait;
use Phug\Util\Partial\EscapeTrait;
use Phug\Util\Partial\TransformableTrait;
use Phug\Util\TransformableInterface;

class ExpressionElement extends AbstractValueElement implements TransformableInterface
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
