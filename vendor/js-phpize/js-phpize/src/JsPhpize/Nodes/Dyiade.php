<?php

namespace JsPhpize\Nodes;

class Dyiade extends Value
{
    /**
     * @var Value
     */
    protected $leftHand;

    /**
     * @var Value
     */
    protected $rightHand;

    /**
     * @var string
     */
    protected $operator;

    public function __construct($operator, Value $leftHand, Value $rightHand)
    {
        $this->operator = $operator;
        $this->leftHand = $leftHand;
        $this->rightHand = $rightHand;
    }
}
