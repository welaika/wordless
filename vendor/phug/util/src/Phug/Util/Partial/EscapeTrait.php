<?php

namespace Phug\Util\Partial;

/**
 * Class EscapeTrait.
 */
trait EscapeTrait
{
    /**
     * @var bool
     */
    private $escaped = false;

    /**
     * @return bool
     */
    public function isEscaped()
    {
        return $this->escaped;
    }

    /**
     * @param bool $escaped
     *
     * @return $this
     */
    public function setIsEscaped($escaped)
    {
        $this->escaped = $escaped;

        return $this;
    }

    /**
     * @return $this
     */
    public function escape()
    {
        $this->escaped = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function unescape()
    {
        $this->escaped = false;

        return $this;
    }
}
