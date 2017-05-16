<?php

namespace JsPhpize\Lexer;

class Token
{
    /**
     * @var array
     */
    protected $data;

    public function __construct($type, array $data)
    {
        $this->data = array_merge(array(
            'type' => $type,
        ), $data);
    }

    public function is($value)
    {
        return in_array($value, array($this->type, $this->value));
    }

    protected function typeIn($values)
    {
        return in_array($this->type, $values);
    }

    protected function valueIn($values)
    {
        return in_array($this->value, $values);
    }

    public function isIn($values)
    {
        $values = is_array($values) ? $values : func_get_args();

        return $this->typeIn($values) || $this->valueIn($values);
    }

    public function isValue()
    {
        return $this->typeIn(array('variable', 'constant', 'string', 'number'));
    }

    protected function isComparison()
    {
        return $this->typeIn(array('===', '!==', '>=', '<=', '<>', '!=', '==', '>', '<'));
    }

    protected function isLogical()
    {
        return $this->typeIn(array('&&', '||', '!'));
    }

    protected function isBinary()
    {
        return $this->typeIn(array('&', '|', '^', '~', '>>', '<<', '>>>'));
    }

    protected function isArithmetic()
    {
        return $this->typeIn(array('+', '-', '/', '*', '%', '**', '--', '++'));
    }

    protected function isVarOperator()
    {
        return $this->typeIn(array('delete', 'void', 'typeof'));
    }

    public function isLeftHandOperator()
    {
        return $this->typeIn(array('~', '!', '--', '++', '-', '+')) || $this->isVarOperator();
    }

    public function isAssignation()
    {
        return substr($this->type, -1) === '=' && !$this->isComparison();
    }

    public function isOperator()
    {
        return $this->isAssignation() || $this->isComparison() || $this->isArithmetic() || $this->isBinary() || $this->isLogical() || $this->isVarOperator();
    }

    public function isNeutral()
    {
        return $this->typeIn(array('comment', 'newline'));
    }

    public function expectNoLeftMember()
    {
        return in_array($this->type, array('!', '~')) || $this->isVarOperator();
    }

    public function isFunction()
    {
        return $this->type === 'function' || $this->type === 'keyword' && $this->value === 'function';
    }

    public function __get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
}
