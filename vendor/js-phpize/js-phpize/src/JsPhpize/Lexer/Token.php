<?php

namespace JsPhpize\Lexer;

class Token extends DataBag
{
    public function isIn($values)
    {
        $values = is_array($values) ? $values : func_get_args();

        return $this->typeIn($values) || $this->valueIn($values);
    }

    public function isValue()
    {
        return $this->typeIn(['variable', 'constant', 'string', 'number', 'regexp']);
    }

    public function isValidMember()
    {
        return $this->typeIn(['variable', 'keyword']);
    }

    protected function isComparison()
    {
        return $this->typeIn(['===', '!==', '>=', '<=', '<>', '!=', '==', '>', '<']);
    }

    protected function isLogical()
    {
        return $this->typeIn(['&&', '||', '!']);
    }

    protected function isBinary()
    {
        return $this->typeIn(['&', '|', '^', '~', '>>', '<<', '>>>']);
    }

    protected function isArithmetic()
    {
        return $this->typeIn(['+', '-', '/', '*', '%', '**', '--', '++']);
    }

    protected function isVarOperator()
    {
        return $this->typeIn(['delete', 'void', 'typeof']);
    }

    public function isLeftHandOperator()
    {
        return $this->typeIn(['~', '!', '--', '++', '-', '+']) || $this->isVarOperator();
    }

    public function isAssignation()
    {
        return mb_substr($this->type, -1) === '=' && !$this->isComparison();
    }

    public function isOperator()
    {
        return $this->isAssignation() || $this->isComparison() || $this->isArithmetic() || $this->isBinary() || $this->isLogical() || $this->isVarOperator();
    }

    public function isNeutral()
    {
        return $this->typeIn(['comment', 'newline']);
    }

    public function expectNoLeftMember()
    {
        return in_array($this->type, ['!', '~']) || $this->isVarOperator();
    }

    public function isFunction()
    {
        return $this->type === 'function' || $this->type === 'keyword' && $this->value === 'function';
    }
}
