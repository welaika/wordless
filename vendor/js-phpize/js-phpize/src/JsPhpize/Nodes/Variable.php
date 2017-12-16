<?php

namespace JsPhpize\Nodes;

class Variable extends Value implements Assignable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $children = array();

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
        $variables = array($this->name);
        foreach ($this->children as $child) {
            $variables = array_merge($variables, $child->getReadVariables());
        }

        return $variables;
    }
}
