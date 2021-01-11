<?php

namespace Phug\Formatter\Partial;

trait HandleVariable
{
    private function isInKeywordParams(&$tokens, $index)
    {
        $buffer = [];
        $index--;
        while ($index >= 0 && count($buffer) < 3) {
            if (is_array($tokens[$index]) && trim($tokens[$index][1]) !== '') {
                $buffer[] = $tokens[$index][0];
            }
            $index--;
        }

        return count($buffer) === 3 &&
            $buffer[0] === T_DOUBLE_ARROW &&
            $buffer[2] === T_AS;
    }

    private function isInFunctionParams(&$tokens, $index)
    {
        $afterOpen = false;
        for ($i = $index - 1; $i >= 0; $i--) {
            if (in_array($tokens[$i], [')', '}'])) {
                break;
            }
            if ($tokens[$i] === '(') {
                $afterOpen = true;
                continue;
            }
            if ($afterOpen && is_array($tokens[$i]) && in_array($tokens[$i][0], [
                T_FUNCTION,
                T_USE,
            ])) {
                return true;
            }
        }

        return false;
    }

    private function isInInterpolation(&$tokens, $index)
    {
        return isset($tokens[$index - 1]) && (
            $tokens[$index - 1] === '"' ||
            is_array($tokens[$index - 1]) &&
            $tokens[$index - 1][0] === T_ENCAPSED_AND_WHITESPACE
        );
    }

    private function isInExclusionContext(&$tokens, $index)
    {
        foreach ([
            // Exclude tokens before the variables
            -1 => [
                T_AS,
                T_EMPTY,
                T_GLOBAL,
                T_ISSET,
                T_OBJECT_OPERATOR,
                T_UNSET,
                T_UNSET_CAST,
                T_VAR,
                T_STATIC,
                T_PRIVATE,
                T_PROTECTED,
                T_PUBLIC,
            ],
            // Exclude tokens after the variables
            1 => [
                '[',
                '=',
                T_AND_EQUAL,
                T_CONCAT_EQUAL,
                T_CURLY_OPEN,
                T_DIV_EQUAL,
                T_DOUBLE_ARROW,
                T_INC,
                T_MINUS_EQUAL,
                T_MOD_EQUAL,
                T_MUL_EQUAL,
                T_OBJECT_OPERATOR,
                T_OR_EQUAL,
                T_PLUS_EQUAL,
                defined('T_POW_EQUAL') ? T_POW_EQUAL : 'T_POW_EQUAL',
                T_SL_EQUAL,
                T_SR_EQUAL,
                T_XOR_EQUAL,
            ],
        ] as $direction => $exclusions) {
            $tokenId = null;
            for ($i = 1; isset($tokens[$index + $direction * $i]); $i++) {
                $tokenId = $tokens[$index + $direction * $i];
                if (is_array($tokenId)) {
                    $tokenId = $tokenId[0];
                }
                // Ignore the following tokens
                if (in_array($tokenId, [
                    T_COMMENT,
                    T_DOC_COMMENT,
                    T_WHITESPACE,
                ])) {
                    continue;
                }
                break;
            }

            if (in_array($tokenId, $exclusions)) {
                return true;
            }
        }

        return false;
    }

    private function isInComplexInterpolation($tokens, $index)
    {
        return isset($tokens[$index - 1]) && is_array($tokens[$index - 1]) && $tokens[$index - 1][0] === T_CURLY_OPEN;
    }

    private function wrapVariableContext($expression, $tokens, $index)
    {
        if (isset($tokens[$index - 1]) && $tokens[$index - 1] === '$') {
            return '{'.$expression.'}';
        }

        if ($this->isInInterpolation($tokens, $index)) {
            return '".'.$expression.'."';
        }

        return $expression;
    }

    public function handleVariable($variable, $index, &$tokens, $checked)
    {
        if (!$checked ||
            $this->isInExclusionContext($tokens, $index) ||
            $this->isInFunctionParams($tokens, $index) ||
            $this->isInKeywordParams($tokens, $index) ||
            $this->isInComplexInterpolation($tokens, $index) ||
            $variable === '$_pug_temp' ||
            mb_substr($variable, 0, 1) !== '$'
        ) {
            return $variable;
        }

        foreach ($this->getOption('checked_variable_exceptions') as $exception) {
            if (call_user_func($exception, $variable, $index, $tokens, $checked)) {
                return $variable;
            }
        }

        return $this->wrapVariableContext('(isset('.$variable.') ? '.$variable.' : null)', $tokens, $index);
    }
}
