<?php

namespace JsPhpize\Lexer;

class DataBag
{
    /**
     * @var array
     */
    protected $data;

    public function __construct($type, array $data)
    {
        $this->data = array_merge(array(
            'type' => $type,
        ), $data);
    }

    public function is($value)
    {
        return in_array($value, array($this->type, $this->value));
    }

    protected function typeIn($values)
    {
        return in_array($this->type, $values);
    }

    protected function valueIn($values)
    {
        return in_array($this->value, $values);
    }

    public function __get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
}
