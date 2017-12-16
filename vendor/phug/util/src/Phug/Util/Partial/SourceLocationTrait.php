<?php

namespace Phug\Util\Partial;

trait SourceLocationTrait
{
    use DocumentLocationTrait;
    use PathTrait;

    /**
     * @var int
     */
    private $offsetLength = 0;

    /**
     * @return int
     */
    public function getOffsetLength()
    {
        return $this->offsetLength;
    }

    /**
     * @param int $offsetLength
     *
     * @return $this
     */
    public function setOffsetLength($offsetLength)
    {
        $this->offsetLength = $offsetLength;

        return $this;
    }
}
