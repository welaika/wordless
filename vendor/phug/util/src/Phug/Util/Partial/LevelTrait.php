<?php

namespace Phug\Util\Partial;

/**
 * Class LevelTrait.
 */
trait LevelTrait
{
    use LevelGetTrait;

    /**
     * @param int $level
     *
     * @return $this
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }
}
