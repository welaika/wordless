<?php

namespace Jade\Nodes;

class Literal extends Node
{
    public $string;

    public function __construct($string)
    {
        // escape the chars '\', '\n', '\r\n' and "'"
        $this->string = preg_replace(array('/\\\\/', '/\\n|\\r\\n/', '/\'/'), array('\\\\', "\r", "\\'"), $string);
    }
}
