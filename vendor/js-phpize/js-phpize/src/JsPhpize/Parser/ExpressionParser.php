<?php

namespace JsPhpize\Parser;

use JsPhpize\Lexer\Token;
use JsPhpize\Nodes\BracketsArray;
use JsPhpize\Nodes\HooksArray;
use JsPhpize\Nodes\Parenthesis;

class ExpressionParser
{
    protected function expressionFromToken($token)
    {
        if (in_array($token->type, array('(', '{', '['))) {
            $this->prepend($token);

            return $this->getExpression();
        }

        return $token;
    }

    protected function getBracketsArray()
    {
        $array = new BracketsArray();
        $isValue = false;
        $key = null;
        $value = null;

        while ($token = $this->next()) {
            if ($token->type === '}') {
                if (!empty($value)) {
                    $array->addItem(strval($key), $value);
                }

                return $array;
            }

            if (!$isValue) {
                switch ($token->type) {
                    case 'newline':
                        continue 2;
                    case 'string':
                        $key = $token->value;
                        break;
                    case 'variable':
                    case 'constant':
                        $key = var_export($token->value, true);
                        break;
                }
                if ($key === null) {
                    $this->unexpected($token);
                }
                $this->expect(':');
                $isValue = true;
                continue;
            }
            if ($token->type === ',') {
                $array->addItem(strval($key), $value);
                $isValue = false;
                $key = null;
                $value = null;
                continue;
            }
            $token = $this->expressionFromToken($token);
            $var = $isValue ? 'value' : 'key';
            ${$var} = ${$var} === null ? $token : ${$var} . $token;
        }

        throw new Exception('Missing } after object values list' . $this->exceptionInfos(), 7);
    }

    protected function getHooksArray()
    {
        $array = new HooksArray();

        $item = '';
        while ($token = $this->next()) {
            if ($token->type === ']') {
                $array->addItem($item);

                return $array;
            }
            if ($token->type === ',') {
                $array->addItem($item);
                $item = '';
                continue;
            }

            $item .= $this->expressionFromToken($token);
        }

        throw new Exception('Missing ] after array items list' . $this->exceptionInfos(), 6);
    }

    protected function getParentheses($goal)
    {
        $parenthesis = new Parenthesis();

        while ($token = $this->next()) {
            if ($token->type === ')') {
                return $parenthesis;
            }

            $parenthesis->addNode($this->expressionFromToken($token));
        }

        throw new Exception('Missing ) after ' . $goal . $this->exceptionInfos(), 5);
    }

    protected function handlePlus(&$expression, $token, $handlePlus)
    {
        if (!$handlePlus && strval($token) === '+') {
            $token = ',';
        } elseif ($handlePlus && ($next = $this->current()) && $next->type === '+') {
            $priorToPlusOperators = array('!', '~', '--', '++', 'typeof', 'void', 'delete', '*', '**', '/', '%');
            $before = array();
            array_pop($expression);
            while ($pop = array_pop($expression)) {
                if ($pop instanceof Token && $pop->type !== '+' && !in_array($pop->type, $priorToPlusOperators)) {
                    $expression[] = $pop;
                    break;
                }
                $before[] = $pop;
            }
            if (count($before)) {
                $before = implode(' ', array_reverse($before));
                $after = array(',', $token, ',');
                while ($this->next() && ($next = $this->current())) {
                    if (in_array($next->type, array('(', '{', '[', 'variable'))) {
                        $next = $this->getExpression(false);
                    }
                    if ($next instanceof Token && !in_array($next->type, $priorToPlusOperators)) {
                        break;
                    }
                    $after[] = $next;
                }
                foreach ($after as &$element) {
                    if (strval($element) === '+') {
                        $element = ',';
                    }
                }
                $token = $this->helperWrap('plus', $before . ' ' . implode(' ', $after));
            }
        } else {
            while (strval(end($expression)) === '+') {
                array_pop($expression);
                $before = array_pop($expression);
                $token = $this->helperWrap('plus', $before, $token);
            }
        }

        $expression[] = $token;
    }

    protected function getExpression($handlePlus = true)
    {
        $expression = array();
        while ($token = $this->next()) {
            if (
                in_array($token->type, array(';', ')', '}', ']')) ||
                ($token->type === 'keyword' && !in_array($token->value, array('true', 'false', 'null', 'function')))
            ) {
                $this->prepend($token);
                break;
            }
            switch ($token->type) {
                case '(':
                    $token = $this->getParentheses('argument list');
                    break;
                case '{':
                    $token = $this->getBracketsArray();
                    break;
                case '[':
                    $token = $this->getHooksArray();
                    break;
                case 'variable':
                    $token = $this->visitVariable($token);
                    break;
            }
            $this->handlePlus($expression, $token, $handlePlus);
        }

        return implode(' ', $expression);
    }

    protected function dyiade(&$variable, $next)
    {
        while ($next && $next->expectRightMember()) {
            $this->skip();
            $variable = $next->type === '+'
                ? $this->helperWrap('plus', $variable, $this->getExpression(false))
                : $variable . ' ' . $next . ' ' . implode(' ', $this->visitToken($this->next()));
            $next = $this->current();
        }
    }

    protected function appendToVariableParts(&$variableParts, $afterNext, $bracket, $parenthesis)
    {
        if ($parenthesis) {
            $variableParts = array('call_user_func(' . $this->helperWrap('dot', $variableParts) . ', ' . $this->getExpression() . ')');

            return;
        }
        $variableParts[] = $bracket
            ? $this->getExpression()
            : var_export($afterNext->value, true);
    }
}
