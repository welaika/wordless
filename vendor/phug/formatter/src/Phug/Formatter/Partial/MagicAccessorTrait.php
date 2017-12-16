<?php

namespace Phug\Formatter\Partial;

trait MagicAccessorTrait
{
    private function getMethod($prefix, $name)
    {
        switch ($name) {
            case 'nodes':
                $name = 'children';
                break;
        }

        return $prefix.ucfirst($name);
    }

    public function __get($name)
    {
        $method = $this->getMethod('get', $name);

        return method_exists($this, $method)
            ? call_user_func([$this, $method])
            : null;
    }

    public function __set($name, $value)
    {
        $method = $this->getMethod('set', $name);

        return method_exists($this, $method)
            ? call_user_func([$this, $method], $value)
            : null;
    }

    public function __isset($name)
    {
        $method = $this->getMethod('get', $name);

        return method_exists($this, $method)
            ? call_user_func([$this, $method]) !== null
            : null;
    }
}
