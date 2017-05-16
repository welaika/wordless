<?php

namespace JsPhpize\Nodes;

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
        $this->nodes = array();
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
}
