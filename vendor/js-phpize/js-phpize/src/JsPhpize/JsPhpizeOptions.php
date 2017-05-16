<?php

namespace JsPhpize;

class JsPhpizeOptions
{
    /**
     * Prefix for specific constants.
     *
     * @const string
     */
    const CONST_PREFIX = '__JPC_';

    /**
     * Prefix for specific variables.
     *
     * @const string
     */
    const VAR_PREFIX = '__jpv_';

    /**
     * Pass options as array or no parameters for all options on default value.
     *
     * @param array $options list of options.
     */
    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    /**
     * Retrieve an option value.
     *
     * @param string $key     option name.
     * @param mixed  $default value to return if the option is not set.
     *
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }

    /**
     * Retrieve the prefix of specific variables.
     *
     * @return string
     */
    public function getVarPrefix()
    {
        return $this->getOption('varPrefix', static::VAR_PREFIX);
    }

    /**
     * Retrieve the prefix of specific constants.
     *
     * @return string
     */
    public function getConstPrefix()
    {
        return $this->getOption('constPrefix', static::CONST_PREFIX);
    }
}
