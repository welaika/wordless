<?php

namespace JsPhpize\Nodes;

abstract class Node
{
    public function __get($name)
    {
        return $this->$name;
    }
}
