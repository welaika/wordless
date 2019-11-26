<?php

namespace Phug\Util\Partial;

/**
 * Class LineGetTrait.
 */
trait LineGetTrait
{
    /**
     * @var int
     */
    private $line = null;

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }
}
