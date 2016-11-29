<?php

namespace JsPhpize\Nodes;

class FunctionCall extends Value
{
    /**
     * @var Value
     */
    protected $function;

    /**
     * @var array
     */
    protected $arguments;

    public function __construct(Value $function, array $arguments)
    {
        $this->function = $function;
        $this->arguments = $arguments;
    }
}
