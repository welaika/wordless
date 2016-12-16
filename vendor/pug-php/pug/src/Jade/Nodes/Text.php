<?php

namespace Jade\Nodes;

class Text extends Node
{
    public $isText = true;
    public $value = '';

    public function __construct($line)
    {
        if (is_string($line)) {
            $this->value = $line;
        }
    }
}
