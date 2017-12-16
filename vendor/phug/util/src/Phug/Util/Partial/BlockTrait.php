<?php

namespace Phug\Util\Partial;

/**
 * Class BlockTrait.
 */
trait BlockTrait
{
    /**
     * @var bool
     */
    private $block = false;

    /**
     * @return bool
     */
    public function isBlock()
    {
        return $this->block;
    }

    /**
     * @param bool $block
     *
     * @return $this
     */
    public function setIsBlock($block)
    {
        $this->block = $block;

        return $this;
    }
}
