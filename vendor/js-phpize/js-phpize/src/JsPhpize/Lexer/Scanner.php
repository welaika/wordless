<?php

namespace JsPhpize\Lexer;

use JsPhpize\JsPhpize;

class Scanner
{
    public function scanComment($matches)
    {
        return $this->valueToken('comment', $matches);
    }

    public function scanNewline($matches)
    {
        return $this->valueToken('newline', $matches);
    }

    public function scanConstant($matches)
    {
        $constant = trim($matches[0]);
        $constPrefix = $this->engine->getOption('constPrefix', JsPhpize::CONST_PREFIX);
        if (strpos($constant, $constPrefix) === 0) {
            throw new Exception('Constants cannot start with ' . $constPrefix . ', this prefix is reserved for JsPhpize' . $this->exceptionInfos(), 1);
        }
        $translate = array(
            'Infinity' => 'INF',
            'NaN' => 'NAN',
            'undefined' => 'null',
        );
        if (isset($translate[$constant])) {
            $constant = $translate[$constant];
        } elseif (substr($matches[0], 0, 5) === 'Math.') {
            $constant = 'M_' . substr($constant, 5);
        }
        $this->consume($matches[0]);

        return $this->token('constant', $constant);
    }

    public function scanKeyword($matches)
    {
        return $this->valueToken('keyword', $matches);
    }

    public function scanLambda($matches)
    {
        return $this->valueToken('lambda', $matches);
    }

    public function scanNumber($matches)
    {
        return $this->valueToken('number', $matches);
    }

    public function scanString($matches)
    {
        return $this->valueToken('string', $matches);
    }

    public function scanOperator($matches)
    {
        return $this->typeToken($matches);
    }

    public function scanVariable($matches)
    {
        $varPrefix = $this->engine->getOption('varPrefix', JsPhpize::VAR_PREFIX);
        if (strpos($matches[1], $varPrefix) === 0) {
            throw new Exception('Variables cannot start with ' . $varPrefix . ', this prefix is reserved for JsPhpize' . $this->exceptionInfos(), 4);
        }

        return $this->valueToken('variable', $matches);
    }
}
