<?php

namespace JsPhpize\Nodes;

/**
 * Class Variable.
 *
 * @property-read string $name     Variable name
 * @property-read Block  $scope    Current block scope
 */
class Variable extends DynamicValue implements Assignable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Block
     */
    protected $scope = null;

    public function __construct($name, array $children)
    {
        $this->name = $name;
        $this->children = $children;
    }

    public function setScope(Block $block)
    {
        $this->scope = $block;
    }

    public function getNonAssignableReason()
    {
        return false;
    }

    public function popChild()
    {
        return array_pop($this->children);
    }

    public function getReadVariables()
    {
        return $this->mergeVariables([$this->name], $this->children);
    }
}
