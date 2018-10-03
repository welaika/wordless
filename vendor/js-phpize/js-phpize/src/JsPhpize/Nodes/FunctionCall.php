<?php

namespace JsPhpize\Nodes;

use JsPhpize\Parser\Exception;

/**
 * Class Value.
 *
 * @property-read Value|Block $function  Function body
 * @property-read array       $arguments List the function arguments passed
 * @property-read null|string $applicant Optional related keyword name (new, clone, ...)
 */
class FunctionCall extends DynamicValue
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
     * @var null|string
     */
    protected $applicant;

    /**
     * FunctionCall constructor.
     *
     * @param Node        $function
     * @param array       $arguments
     * @param array       $children
     * @param null|string $applicant
     *
     * @throws Exception
     */
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
        return $this->mergeVariables($this->function->getReadVariables(), $this->arguments);
    }
}
