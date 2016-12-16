<?php

namespace Jade\Nodes;

// cant use the keyword case as class name
class CaseNode extends Node
{
    public $expr;
    public $block;

    public function __construct($expr, $block = null)
    {
        $this->expr = $expr;
        $this->block = $block;
    }
}
