<?php

namespace JsPhpize\Nodes;

/**
 * Class Constant.
 *
 * @property-read Node          $value                block parenthesis or inline content
 * @property-read Instruction[] $instructions         block body (list of instructions)
 * @property-read string        $type                 block type
 * @property-read bool          $inInstruction        true if the block is in an Instruction instance
 * @property-read bool          $multipleInstructions true if the block contains more than one instruction
 * @property-read array         $letVariables         true if the block contains more than one instruction
 */
class Block extends Node
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var Node
     */
    protected $value;

    /**
     * @var array
     */
    protected $instructions;

    /**
     * @var bool
     */
    protected $inInstruction;

    /**
     * @var bool
     */
    protected $multipleInstructions = false;

    /**
     * @var array
     */
    protected $letVariables = [];

    public function __construct($type)
    {
        $this->type = $type;
        $this->instructions = [];
        $this->inInstruction = false;
    }

    public function let($variable)
    {
        $this->letVariables[] = $variable;
    }

    public function getLetVariables()
    {
        $scope = $this;

        return array_map(function ($name) use ($scope) {
            $variable = new Variable($name, []);
            $variable->setScope($scope);

            return $variable;
        }, $this->letVariables);
    }

    public function isLet($variable)
    {
        return in_array($variable, $this->letVariables);
    }

    public function handleInstructions()
    {
        return $this->needParenthesis() || in_array($this->type, [
            'main',
            'else',
            'try',
            'finally',
            'do',
            'interface',
            'class',
            'switch',
        ]);
    }

    public function needParenthesis()
    {
        return in_array($this->type, [
            'if',
            'elseif',
            'catch',
            'for',
            'while',
            'function',
        ]);
    }

    public function addInstructions($instructions)
    {
        $instructions = is_array($instructions) ? $instructions : func_get_args();
        if (count($instructions)) {
            if (!$this->inInstruction) {
                $this->inInstruction = true;
                $this->instructions[] = new Instruction();
            }
            foreach ($instructions as $instruction) {
                $this->instructions[count($this->instructions) - 1]->add($instruction);
            }
        }
    }

    public function addInstruction()
    {
        $this->addInstructions(func_get_args());
    }

    public function endInstruction()
    {
        $this->inInstruction = false;
    }

    public function setValue(Node $value)
    {
        if ($this->type === 'for' && $value instanceof Parenthesis && $value->separator !== 'in') {
            $value->setSeparator(';');
        }

        $this->value = $value;
    }

    public function enableMultipleInstructions()
    {
        $this->multipleInstructions = true;
    }

    public function getReadVariables()
    {
        $variables = $this->value->getReadVariables();
        foreach ($this->instructions as $instruction) {
            $variables = array_merge($variables, $instruction->getReadVariables());
        }
        $variables = array_unique($variables);
        if ($this->type === 'function') {
            $nodes = isset($this->value, $this->value->nodes) ? $this->value->nodes : [];
            if (count($nodes)) {
                $nodes = array_map(function ($node) {
                    return $node instanceof Variable ? $node->name : null;
                }, $nodes);
                $variables = array_filter($variables, function ($variable) use ($nodes) {
                    return !in_array($variable, $nodes);
                });
            }
        }

        return $variables;
    }
}
