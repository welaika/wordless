<?php

namespace Phug\Util\Partial;

/**
 * Class PathGetTrait.
 */
trait PathGetTrait
{
    /**
     * @var string
     */
    private $path = null;

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
