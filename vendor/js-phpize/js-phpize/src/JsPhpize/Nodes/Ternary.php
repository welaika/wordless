<?php

namespace JsPhpize\Nodes;

/**
 * Class Dyiade.
 *
 * @property-read Value $condition  value used as ternary condition
 * @property-read Value $trueValue  returned value if the ternary condition is truthy
 * @property-read Value $falseValue returned value if the ternary condition is falsy
 */
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

    public function getReadVariables()
    {
        return array_merge(
            $this->condition->getReadVariables(),
            $this->trueValue->getReadVariables(),
            $this->falseValue->getReadVariables()
        );
    }
}
