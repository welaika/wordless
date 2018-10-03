<?php

namespace JsPhpize\Nodes;

class BracketsArray extends ArrayBase
{
    public function addItem(Constant $key, Node $value)
    {
        $this->data[] = [$key, $value];
    }

    public function getReadVariables()
    {
        $variables = [];
        foreach ($this->data as $data) {
            $variables = array_merge($variables, $data[1]->getReadVariables());
        }

        return $variables;
    }
}
