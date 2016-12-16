<?php

namespace Jade\Nodes;

class Code extends Node
{
    public $value;
    public $buffer;
    public $escape;

    public function __construct($value, $buffer, $escape)
    {
        $this->value = $value;
        $this->buffer = $buffer;
        $this->escape = $escape;
    }
}
