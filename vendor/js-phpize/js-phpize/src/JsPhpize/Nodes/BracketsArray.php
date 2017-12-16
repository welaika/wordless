<?php

namespace JsPhpize\Nodes;

class BracketsArray extends ArrayBase
{
    public function addItem(Constant $key, Node $value)
    {
        $this->data[] = array($key, $value);
    }

    public function getReadVariables()
    {
        $variables = array();
        foreach ($this->data as $data) {
            $variables = array_merge($variables, $data[1]->getReadVariables());
        }

        return $variables;
    }
}
