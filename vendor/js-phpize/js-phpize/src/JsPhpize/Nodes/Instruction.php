<?php

namespace JsPhpize\Nodes;

use JsPhpize\Parser\Exception;

class Instruction extends Node
{
    /**
     * @var array
     */
    protected $instructions;

    public function add($instruction)
    {
        if (!is_object($instruction)) {
            throw new Exception('An instance of Assignation or Value was expected, ' . gettype($instruction) . ' value type given.', 10);
        }

        if (!$instruction instanceof Value
        && !$instruction instanceof Block) {
            throw new Exception('An instance of Block or Value was expected, ' . get_class($instruction) . ' instance given.', 10);
        }

        $this->instructions[] = $instruction;
    }
}
