<?php

namespace Jade\Nodes;

/**
 * Class Jade\Nodes\Attributes.
 */
class Attributes extends Node
{
    /**
     * @var array
     */
    public $attributes = array();

    /**
     * @param      $name
     * @param      $value
     * @param bool $escaped
     *
     * @return $this
     */
    public function setAttribute($name, $value, $escaped = false)
    {
        $this->attributes[] = compact('name', 'value', 'escaped');

        return $this;
    }

    /**
     * @param $name
     */
    public function removeAttribute($name)
    {
        foreach ($this->attributes as $k => $attr) {
            if ($attr['name'] === $name) {
                unset($this->attributes[$k]);
            }
        }
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getAttribute($name)
    {
        foreach ($this->attributes as $attr) {
            if ($attr['name'] === $name) {
                return $attr;
            }
        }
    }
}
