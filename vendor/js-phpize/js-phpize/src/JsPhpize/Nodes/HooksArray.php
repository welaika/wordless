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
}
