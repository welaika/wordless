<?php

namespace JsPhpize\Parser;

use JsPhpize\Lexer\Token;
use JsPhpize\Nodes\Assignation;
use JsPhpize\Nodes\Constant;
use JsPhpize\Nodes\Dyiade;
use JsPhpize\Nodes\FunctionCall;
use JsPhpize\Nodes\Variable;

abstract class TokenExtractor extends TokenCrawler
{
    protected function getBracketsArrayItemKeyFromToken($token)
    {
        $typeAndValue = new BracketsArrayItemKey($token);

        if ($typeAndValue->isValid()) {
            list($type, $value) = $typeAndValue->get();
            $token = $this->next();

            if (!$token) {
                throw new Exception('Missing value after ' . $value . $this->exceptionInfos(), 12);
            }

            if (!$token->is(':')) {
                throw $this->unexpected($token);
            }

            $key = new Constant($type, $value);
            $value = $this->expectValue($this->next());

            return [$key, $value];
        }
    }

    protected function getVariableChildFromToken(Token $token)
    {
        if ($token->is('.')) {
            $this->skip();
            $token = $this->next();

            if ($token && $token->isValidMember()) {
                return new Constant('string', var_export($token->value, true), true);
            }

            throw $this->unexpected($token);
        }

        if ($token->is('[')) {
            $exceptionInfos = $this->exceptionInfos();
            $this->skip();
            $value = $this->expectValue($this->next());

            $token = $this->next();

            if (!$token) {
                throw new Exception('Missing ] to match ' . $exceptionInfos, 13);
            }

            if ($token->is(']')) {
                return $value;
            }

            throw $this->unexpected($token);
        }
    }

    protected function getInstructionFromToken($token)
    {
        if ($token->type === 'keyword') {
            return $this->parseKeyword($token);
        }

        if ($value = $this->getValueFromToken($token)) {
            return $value;
        }
    }

    protected function getValueFromToken($token, $previousToken = null, $applicant = null)
    {
        $value = $this->getInitialValue($token);
        if ($value) {
            $this->appendFunctionsCalls($value, $previousToken, $applicant);
        }

        return $value;
    }

    protected function handleOptionalValue($keyword, $afterKeyword, $applicant)
    {
        if ($afterKeyword && !$afterKeyword->is(';')) {
            $value = $this->expectValue($this->next(), $keyword, $applicant);
            $keyword->setValue($value);
        }
    }

    protected function handleParentheses($keyword, $afterKeyword)
    {
        if ($afterKeyword && $afterKeyword->is('(')) {
            $this->skip();
            $keyword->setValue($this->parseParentheses($keyword->type === 'for' ? [';', 'in'] : [',', ';']));
        } elseif ($keyword->needParenthesis()) {
            throw new Exception("'" . $keyword->type . "' block need parentheses.", 17);
        }
    }

    protected function handleLeftOperator($value, $token)
    {
        if ($token->type === 'typeof') {
            if ($value instanceof Dyiade) {
                return new Dyiade(
                    $value->operator,
                    new FunctionCall(new Variable('gettype', []), [$value->leftHand], []),
                    $value->rightHand,
                    $value->before,
                    $value->after
                );
            }

            return new FunctionCall(new Variable('gettype', []), [$value], []);
        }

        return $value->prepend($token->type);
    }

    protected function getInitialValue($token)
    {
        if ($token->isFunction()) {
            return $this->parseFunction();
        }
        if ($token->is('(')) {
            return $this->parseParentheses();
        }
        if ($token->is('[')) {
            return $this->parseHooksArray();
        }
        if ($token->is('{')) {
            return $this->parseBracketsArray();
        }
        if ($token->isLeftHandOperator()) {
            return $this->handleLeftOperator($this->expectValue($this->next(), $token), $token);
        }
        if ($token->isValue()) {
            return $this->parseValue($token);
        }
        if ($token->isIn(['new', 'clone'])) {
            return $this->parseKeyword($token);
        }
    }

    protected function appendFunctionsCalls(&$value, $previousToken = null, $applicant = null)
    {
        while ($token = $this->get(0)) {
            if ($token->is('{') || $token->expectNoLeftMember()) {
                throw $this->unexpected($this->next());
            }

            if ($token->is('?')) {
                $this->skip();
                $value = $this->parseTernary($value);

                continue;
            }

            if ($token->is('(')) {
                $this->skip();
                $value = $this->parseFunctionCallChildren($value, $applicant);

                continue;
            }

            if ($token->is('instanceof')) {
                $this->skip();
                $nextValue = $this->expectValue($this->next(), $previousToken);
                if ($nextValue instanceof Variable) {
                    $nextValue = new Constant('constant', $nextValue->name);
                }

                $value = $nextValue instanceof Constant && in_array($nextValue->value, ['Array', 'Object', 'String'])
                    ? new FunctionCall(new Variable('is_' . strtolower($nextValue->value), []), [$value], [])
                    : new Dyiade('instanceof', $value, $nextValue);

                continue;
            }

            if ($token->isOperator()) {
                if ($token->isIn('++', '--')) {
                    $value->append($this->next()->type);

                    break;
                }

                if ($token->isAssignation()) {
                    $this->skip();
                    $valueToAssign = $this->expectValue($this->next(), $previousToken);
                    $value = new Assignation($token->type, $value, $valueToAssign);

                    continue;
                }

                $this->skip();
                $nextValue = $this->expectValue($this->next(), $previousToken);
                $value = new Dyiade($token->type, $value, $nextValue);
                $this->get(0);

                continue;
            }

            break;
        }
    }

    protected function expectValue($next, $token = null, $applicant = null)
    {
        if (!$next) {
            if ($token) {
                throw $this->unexpected($token);
            }

            throw new Exception('Value expected after ' . $this->exceptionInfos(), 20);
        }

        $value = $this->getValueFromToken($next, $token, $applicant);
        if (!$value) {
            throw $this->unexpected($next);
        }

        return $value;
    }

    protected function expectColon($errorMessage, $errorCode)
    {
        $colon = $this->next();
        if (!$colon || !$colon->is(':')) {
            throw new Exception($errorMessage, $errorCode);
        }
    }
}
