<?php

namespace JsPhpize\Nodes;

use JsPhpize\Parser\Exception;

class Assignation extends Value
{
    /**
     * @var Assignable
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

    public function __construct($operator, Assignable $leftHand, Node $rightHand)
    {
        $reason = $leftHand->getNonAssignableReason();

        if ($reason !== false) {
            throw new Exception($reason, 9);
        }

        $this->operator = $operator;
        $this->leftHand = $leftHand;
        $this->rightHand = $rightHand;
    }

    public function getReadVariables()
    {
        return array_merge(
            $this->leftHand->getReadVariables(),
            $this->rightHand->getReadVariables()
        );
    }
}
