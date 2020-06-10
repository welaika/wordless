<?php

namespace Phug\Partial;

use ArrayObject;
use Phug\OptionsBundle;

trait FacadeOptionsTrait
{
    /**
     * @var OptionsBundle
     */
    private static $options = null;

    protected static function resetFacadeOptions()
    {
        static::$options = null;
    }

    protected static function callOption($method, array $arguments)
    {
        if (!static::$options) {
            static::$options = new OptionsBundle();
        }

        return call_user_func_array([static::$options, $method], $arguments);
    }

    protected static function isOptionMethod($method)
    {
        return in_array($method, [
            'hasOption',
            'getOption',
            'setOption',
            'setOptions',
            'setOptionsRecursive',
            'setOptionsDefaults',
            'unsetOption',
        ]);
    }

    protected static function getFacadeOptions()
    {
        $options = static::$options ? (static::$options->getOptions() ?: []) : [];

        return $options instanceof ArrayObject ? $options->getArrayCopy() : $options;
    }
}
