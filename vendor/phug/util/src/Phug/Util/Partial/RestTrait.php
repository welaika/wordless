<?php

namespace Phug\Util\Partial;

/**
 * Class RestTrait.
 */
trait RestTrait
{
    /**
     * @var bool
     */
    private $rest = false;

    /**
     * @return bool
     */
    public function isRest()
    {
        return $this->rest;
    }

    /**
     * @param bool $rest
     *
     * @return $this
     */
    public function setIsRest($rest)
    {
        $this->rest = $rest;

        return $this;
    }
}
