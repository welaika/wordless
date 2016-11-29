<?php

namespace JsPhpize\Nodes;

class Main extends Block
{
    public function __construct($parentheses = null)
    {
        parent::__construct('main', $parentheses);
    }
}
