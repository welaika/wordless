<?php

namespace JsPhpize\Nodes;

class Instruction extends Node
{
    /**
     * @var array
     */
    protected $instructions;

    public function add($instruction)
    {
        $this->instructions[] = $instruction;
    }
}
