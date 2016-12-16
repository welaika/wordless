<?php

namespace JsPhpize\Nodes;

class Ternary extends Value
{
    /**
     * @var Value
     */
    protected $condition;

    /**
     * @var Value
     */
    protected $trueValue;

    /**
     * @var Value
     */
    protected $falseValue;

    public function __construct(Value $condition, Value $trueValue, Value $falseValue)
    {
        $this->condition = $condition;
        $this->trueValue = $trueValue;
        $this->falseValue = $falseValue;
    }
}
