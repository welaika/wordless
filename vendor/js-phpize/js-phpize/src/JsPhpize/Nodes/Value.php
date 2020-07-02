<?php

namespace JsPhpize\Nodes;

/**
 * Class Value.
 *
 * @property-read array $before List prefixed codes
 * @property-read array $after  List suffixed codes
 */
abstract class Value extends Node
{
    /**
     * @var array
     */
    protected $before = [];

    /**
     * @var array
     */
    protected $after = [];

    public function getBefore()
    {
        return implode(' ', $this->before);
    }

    public function prepend($before)
    {
        array_unshift($this->before, $before);

        return $this;
    }

    public function getAfter()
    {
        return implode(' ', $this->after);
    }

    public function append($after)
    {
        $this->after[] = $after;

        return $this;
    }
}
