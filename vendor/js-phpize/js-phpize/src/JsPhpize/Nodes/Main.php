<?php

namespace JsPhpize\Nodes;

class Main extends Block
{
    /**
     * @var bool
     */
    protected $multipleInstructions = true;

    public function __construct()
    {
        parent::__construct('main');
    }
}
