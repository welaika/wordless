<?php

namespace JsPhpize\Parser;

use JsPhpize\Lexer\Token;

class BracketsArrayItemKey
{
    /**
     * @var Token
     */
    protected $token;

    /**
     * @var array
     */
    protected $typeAndValue;

    public function __construct(Token $token)
    {
        $this->token = $token;
        $this->typeAndValue = $this->parseTypeAndValue();
    }

    protected function getStringExport($value)
    {
        return ['string', var_export($value, true)];
    }

    protected function parseTypeAndValue()
    {
        $token = $this->token;

        if ($token->type === 'keyword') {
            return $this->getStringExport($token->value);
        }

        if ($token->isValue()) {
            $type = $token->type;
            $value = $token->value;

            if ($type === 'variable') {
                return $this->getStringExport($value);
            }

            return [$token->type, $token->value];
        }

        return [null, null];
    }

    public function isValid()
    {
        return !is_null($this->typeAndValue[0]);
    }

    public function get()
    {
        return $this->typeAndValue;
    }
}
