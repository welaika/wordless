<?php

namespace JsPhpize\Nodes;

abstract class Value extends Node
{
    /**
     * @var array
     */
    protected $before = array();

    /**
     * @var array
     */
    protected $after = array();

    public function getBefore()
    {
        return implode(' ', $this->before);
    }

    public function prepend($before)
    {
        array_unshift($this->before, $before);
    }

    public function getAfter()
    {
        return implode(' ', $this->after);
    }

    public function append($after)
    {
        $this->after[] = $after;
    }
}
