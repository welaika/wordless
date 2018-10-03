<?php

namespace JsPhpize\Nodes;

/**
 * Class Parenthesis.
 *
 * @property-read array  $nodes     parenthesis items
 * @property-read string $separator string separator used between parenthesis items
 */
class Parenthesis extends Value
{
    /**
     * @var array
     */
    protected $nodes;

    /**
     * @var string
     */
    protected $separator = ',';

    public function __construct()
    {
        $this->nodes = [];
    }

    public function addNodes($nodes)
    {
        $nodes = array_filter(is_array($nodes) ? $nodes : func_get_args());
        foreach ($nodes as $node) {
            $this->nodes[] = $node;
        }
    }

    public function addNode()
    {
        $this->addNodes(func_get_args());
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }

    public function getReadVariables()
    {
        $variables = [];
        foreach ($this->nodes as $node) {
            $variables = array_merge($variables, $node->getReadVariables());
        }

        return $variables;
    }
}
