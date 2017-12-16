<?php

namespace Phug\Util;

interface SourceLocationInterface extends DocumentLocationInterface
{
    /**
     * @return int
     */
    public function getOffsetLength();

    public function setOffsetLength($offsetLength);

    /**
     * @return string
     */
    public function getPath();
}
