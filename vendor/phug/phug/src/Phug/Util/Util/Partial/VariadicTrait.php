<?php

namespace Phug\Util\Partial;

/**
 * Class VariadicTrait.
 */
trait VariadicTrait
{
    /**
     * @var bool
     */
    private $variadic = false;

    /**
     * @return bool
     */
    public function isVariadic()
    {
        return $this->variadic;
    }

    /**
     * @param bool $escaped
     *
     * @return $this
     */
    public function setIsVariadic($variadic)
    {
        $this->variadic = $variadic;

        return $this;
    }
}
