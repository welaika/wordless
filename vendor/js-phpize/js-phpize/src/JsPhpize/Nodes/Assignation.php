<?php

namespace JsPhpize\Nodes;

use JsPhpize\Parser\Exception;

/**
 * Class Assignation.
 *
 * @property-read Assignable $leftHand  left hand assignation slot
 * @property-read Node       $rightHand right hand assigned value
 * @property-read string     $operator  assignation operator
 */
class Assignation extends Value
{
    /**
     * @var Assignable
     */
    protected $leftHand;

    /**
     * @var Node
     */
    protected $rightHand;

    /**
     * @var string
     */
    protected $operator;

    /**
     * Assignation constructor.
     *
     * @param string     $operator
     * @param Assignable $leftHand
     * @param Node       $rightHand
     *
     * @throws Exception
     */
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
            method_exists($this->leftHand, 'getReadVariables') ? $this->leftHand->getReadVariables() : [],
            $this->rightHand->getReadVariables()
        );
    }
}
