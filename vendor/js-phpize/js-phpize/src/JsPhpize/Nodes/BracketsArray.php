<?php

namespace JsPhpize\Nodes;

class BracketsArray extends ArrayBase
{
    public function addItem(Constant $key, Node $value)
    {
        $value->mustBeAssignable();

        $this->data[] = array($key, $value);
    }
}
