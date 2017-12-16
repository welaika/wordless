<?php

namespace Pug;

use Phug\Phug;

/**
 * Class Pug\Pug.
 */
class Facade
{
    /**
     * Set Pug\Pug as the default Phug rendering engine then call static method through the Phug facade.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        Pug::init();

        return call_user_func_array([Phug::class, $name], $arguments);
    }
}
