<?php

namespace JsPhpize\Nodes;

class Main extends Block
{
    /**
     * @var bool
     */
    protected $multipleInstructions = true;

    public function __construct($parentheses = null)
    {
        parent::__construct('main', $parentheses);
    }
}
