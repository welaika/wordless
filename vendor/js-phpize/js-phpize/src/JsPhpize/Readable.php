<?php

namespace JsPhpize;

abstract class Readable
{
    public function __get($name)
    {
        return $this->$name;
    }

    public function __isset($name)
    {
        return isset($this->$name);
    }

    public function getReadVariables()
    {
        return [];
    }
}
