<?php

namespace JsPhpize\Nodes;

use JsPhpize\Parser\Exception;

class FunctionCall extends Value
{
    /**
     * @var Value|Block
     */
    protected $function;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var array
     */
    protected $children;

    /**
     * @var null|string
     */
    protected $applicant;

    public function __construct(Node $function, array $arguments, array $children, $applicant = null)
    {
        if (!($function instanceof Value || $function instanceof Block)) {
            throw new Exception('Unexpected called type ' . get_class($function), 24);
        }

        $this->function = $function;
        $this->arguments = $arguments;
        $this->applicant = $applicant;
        $this->children = $children;
    }

    public function getReadVariables()
    {
        $variables = $this->function->getReadVariables();
        foreach ($this->arguments as $argument) {
            $variables = array_merge($variables, $argument->getReadVariables());
        }

        return $variables;
    }
}
