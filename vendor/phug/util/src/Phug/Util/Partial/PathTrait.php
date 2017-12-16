<?php

namespace Phug\Util\Partial;

/**
 * Class PathTrait.
 */
trait PathTrait
{
    use PathGetTrait;

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }
}
