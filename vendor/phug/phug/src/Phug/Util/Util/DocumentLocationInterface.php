<?php

namespace Phug\Util;

/**
 * Interface DocumentLocationInterface.
 */
interface DocumentLocationInterface
{
    /**
     * @return int
     */
    public function getLine();

    /**
     * @return int
     */
    public function getOffset();
}
