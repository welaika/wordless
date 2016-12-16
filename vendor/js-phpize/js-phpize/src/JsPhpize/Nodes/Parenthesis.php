<?php

namespace JsPhpize\Nodes;

use JsPhpize\Parse\Exception;

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
            if (!$node instanceof Value) {
                throw new Exception('Every node in a parenthesis must be an instance of Value.', 11);
            }
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
