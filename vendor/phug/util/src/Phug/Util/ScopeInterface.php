<?php

namespace Phug\Util;

/**
 * Interface ScopeInterface.
 */
interface ScopeInterface
{
    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions($options);

    /**
     * @return string
     */
    public function getOptions();
}
