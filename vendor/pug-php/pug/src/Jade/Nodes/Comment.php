<?php

namespace Jade\Nodes;

class Comment extends Node
{
    public $value;
    public $buffer;

    public function __construct($value, $buffer)
    {
        $this->value = $value;
        $this->buffer = $buffer;
    }
}
