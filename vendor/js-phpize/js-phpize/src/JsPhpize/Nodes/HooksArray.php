<?php

namespace JsPhpize\Nodes;

class HooksArray extends ArrayBase
{
    public function addItem(Value $value)
    {
        if (!empty($value)) {
            $this->data[] = $value;
        }
    }

    public function getReadVariables()
    {
        $variables = array();
        foreach ($this->data as $value) {
            $variables = array_merge($variables, $value->getReadVariables());
        }

        return $variables;
    }
}
