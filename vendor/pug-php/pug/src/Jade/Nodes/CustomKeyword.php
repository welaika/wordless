<?php

namespace Jade\Nodes;

class CustomKeyword extends Node
{
    public $keyWord;
    public $args;
    public $block;

    public function __construct($keyWord, $args, $block = null)
    {
        $this->keyWord = $keyWord;
        $this->args = $args;
        $this->block = $block;
    }
}
