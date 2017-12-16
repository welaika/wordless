<?php

namespace Pug;

/**
 * Interface Pug\FilterInterface.
 */
interface FilterInterface
{
    /**
     * @param string     $code
     * @param array|null $options
     *
     * @return string
     */
    public function __invoke($code, array $options = null);
}
