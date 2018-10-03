<?php

namespace JsPhpize\Nodes;

/**
 * Class Dyiade.
 *
 * @property-read Value  $leftHand  left hand dyiade value
 * @property-read Value  $rightHand right hand dyiade value
 * @property-read string $operator  dyiade operator
 */
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

    public function getReadVariables()
    {
        return array_merge($this->leftHand->getReadVariables(), $this->rightHand->getReadVariables());
    }
}
