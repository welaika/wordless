<?php

namespace Phug\Util\Partial;

/**
 * Class ValueTrait.
 */
trait ValueTrait
{
    use StaticMemberTrait;

    /**
     * @var mixed
     */
    private $value = null;

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function hasStaticValue()
    {
        return $this->hasStaticMember('value');
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
