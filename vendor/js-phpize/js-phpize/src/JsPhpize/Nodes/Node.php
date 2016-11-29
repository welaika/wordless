<?php

namespace JsPhpize\Nodes;

use JsPhpize\Parse\Exception;

abstract class Node
{
    public function __get($name)
    {
        return $this->$name;
    }

    public function mustBeAssignable()
    {
        if (!($this instanceof Value) && (!($this instanceof Block) || $this->type !== 'function')) {
            throw new Exception('Only Value instance or Function block could be assigned.', 19);
        }
    }
}
