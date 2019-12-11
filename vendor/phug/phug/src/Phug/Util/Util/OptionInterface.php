<?php

namespace Phug\Util;

use ArrayAccess;

/**
 * Interface OptionInterface.
 */
interface OptionInterface
{
    /**
     * @param string|array $name
     *
     * @return mixed
     */
    public function hasOption($name);

    /**
     * @param string|array $name
     *
     * @return mixed
     */
    public function getOption($name);

    /**
     * @param string|array $name
     * @param mixed        $value
     *
     * @return $this
     */
    public function setOption($name, $value);

    /**
     * @return array|ArrayAccess
     */
    public function getOptions();

    /**
     * @param array|ArrayAccess $options
     *
     * @return $this
     */
    public function setOptions($options);

    /**
     * @param array|ArrayAccess $options
     *
     * @return $this
     */
    public function setOptionsRecursive($options);

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptionsDefaults($options);

    /**
     * @param string|array $name
     *
     * @return $this
     */
    public function unsetOption($name);
}
