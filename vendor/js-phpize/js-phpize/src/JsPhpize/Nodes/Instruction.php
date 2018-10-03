<?php

namespace JsPhpize\Nodes;

/**
 * Class Instruction.
 *
 * @property-read array $instructions block body (list of instructions)
 * @property-read bool  $appendReturn true if it should return the last instruction
 */
class Instruction extends Node
{
    /**
     * @var array
     */
    protected $instructions;

    /**
     * @var bool
     */
    protected $appendReturn;

    public function add($instruction)
    {
        $this->instructions[] = $instruction;
    }

    public function prependReturn()
    {
        $this->appendReturn = true;
    }

    public function isReturnPrepended()
    {
        return $this->appendReturn === true;
    }

    public function getReadVariables()
    {
        $variables = [];
        foreach ($this->instructions as $instruction) {
            $variables = array_merge($variables, $instruction->getReadVariables());
        }

        return $variables;
    }
}
