<?php

namespace JsPhpize\Parser;

use JsPhpize\JsPhpize;
use JsPhpize\Lexer\Lexer;
use JsPhpize\Nodes\Assignation;
use JsPhpize\Nodes\Block;
use JsPhpize\Nodes\BracketsArray;
use JsPhpize\Nodes\Constant;
use JsPhpize\Nodes\FunctionCall;
use JsPhpize\Nodes\HooksArray;
use JsPhpize\Nodes\Main;
use JsPhpize\Nodes\Parenthesis;
use JsPhpize\Nodes\Ternary;
use JsPhpize\Nodes\Value;
use JsPhpize\Nodes\Variable;

class Parser extends TokenExtractor
{
    /**
     * @var JsPhpize
     */
    protected $engine;

    /**
     * @var array
     */
    protected $dependencies;

    /**
     * @var array
     */
    protected $stack;

    public function __construct(JsPhpize $engine, $input, $filename)
    {
        $input = str_replace(["\r\n", "\r"], ["\n", ''], $input);
        $this->tokens = [];
        $this->dependencies = [];
        $this->engine = $engine;
        $this->lexer = new Lexer($engine, $input, $filename);
    }

    public function rest()
    {
        return $this->lexer->rest();
    }

    protected function parseLambda(Value $parameters)
    {
        $lambda = new Block('function');
        $lambda->setValue($parameters);
        $next = $this->next();

        if ($next) {
            if ($next->is('{')) {
                $this->parseBlock($lambda);
                $this->skip();

                return $lambda;
            }

            $return = new Block('return');
            $return->setValue($this->expectValue($next));
            $lambda->addInstruction($return);
        }

        return $lambda;
    }

    protected function parseParentheses($allowedSeparators = [',', ';'])
    {
        $parentheses = new Parenthesis();
        $exceptionInfos = $this->exceptionInfos();
        $expectComma = false;

        while ($token = $this->next()) {
            if ($token->is(')')) {
                $next = $this->get(0);

                if ($next && $next->type === 'lambda') {
                    $this->skip();

                    return $this->parseLambda($parentheses);
                }

                return $parentheses;
            }

            if ($expectComma) {
                if ($token->is('in') && in_array('in', $allowedSeparators)) {
                    $parentheses->setSeparator('in');
                    $expectComma = false;

                    continue;
                }

                if ($token->isIn(',', ';') && in_array($token->type, $allowedSeparators)) {
                    $expectComma = false;

                    continue;
                }

                throw $this->unexpected($token);
            }

            if ($value = $this->getValueFromToken($token)) {
                $expectComma = true;
                $parentheses->addNode($value);

                continue;
            }

            if ($token->type === 'keyword' && $token->valueIn(['var', 'const', 'let'])) {
                // @TODO handle let scope here
                // if ($token->value === 'let') {
                //     $this->letForNextBlock = $this->parseLet();
                // }

                continue;
            }

            throw $this->unexpected($token);
        }

        if ($this->engine->getOption('allowTruncatedParentheses')) {
            $this->engine->setFlag(JsPhpize::FLAG_TRUNCATED_PARENTHESES, true);

            return $parentheses;
        }

        throw new Exception('Missing ) to match ' . $exceptionInfos, 5);
    }

    protected function parseHooksArray()
    {
        $array = new HooksArray();
        $exceptionInfos = $this->exceptionInfos();
        $expectComma = false;

        while ($token = $this->next()) {
            if ($token->is(']')) {
                return $array;
            }

            if ($expectComma) {
                if ($token->is(',')) {
                    $expectComma = false;

                    continue;
                }

                throw $this->unexpected($token);
            }

            if ($value = $this->getValueFromToken($token)) {
                $expectComma = true;
                $array->addItem($value);

                continue;
            }

            throw $this->unexpected($token);
        }

        throw new Exception('Missing ] to match ' . $exceptionInfos, 6);
    }

    protected function parseBracketsArray()
    {
        $array = new BracketsArray();
        $exceptionInfos = $this->exceptionInfos();
        $expectComma = false;

        while ($token = $this->next()) {
            if ($token->is('}')) {
                return $array;
            }

            if ($expectComma) {
                if ($token->is(',')) {
                    $expectComma = false;

                    continue;
                }

                throw $this->unexpected($token);
            }

            if ($pair = $this->getBracketsArrayItemKeyFromToken($token)) {
                list($key, $value) = $pair;
                $expectComma = true;
                $array->addItem($key, $value);

                continue;
            }

            throw $this->unexpected($token);
        }

        throw new Exception('Missing } to match ' . $exceptionInfos, 7);
    }

    protected function parseFunctionCallChildren($function, $applicant = null)
    {
        $arguments = $this->parseParentheses()->nodes;
        $children = [];

        while ($next = $this->get(0)) {
            if ($value = $this->getVariableChildFromToken($next)) {
                $children[] = $value;

                $next = $this->get(0);

                if ($next && $next->is('(')) {
                    $this->skip();

                    return $this->parseFunctionCallChildren(
                        new FunctionCall($function, $arguments, $children, $applicant)
                    );
                }

                continue;
            }

            break;
        }

        return new FunctionCall($function, $arguments, $children, $applicant);
    }

    protected function parseVariable($name)
    {
        $children = [];
        $variable = null;

        while ($next = $this->get(0)) {
            if ($next->type === 'lambda') {
                $this->skip();
                $parenthesis = new Parenthesis();
                $parenthesis->addNode(new Variable($name, $children));

                return $this->parseLambda($parenthesis);
            }

            if ($value = $this->getVariableChildFromToken($next)) {
                $children[] = $value;

                $next = $this->get(0);

                if ($next && $next->is('(')) {
                    $this->skip();

                    $variable = $this->parseFunctionCallChildren(new Variable($name, $children));

                    break;
                }

                continue;
            }

            break;
        }

        if ($variable === null) {
            $variable = new Variable($name, $children);

            for ($i = count($this->stack) - 1; $i >= 0; $i--) {
                $block = $this->stack[$i];

                if ($block->isLet($name)) {
                    $variable->setScope($block);

                    break;
                }
            }
        }

        return $variable;
    }

    protected function parseTernary(Value $condition)
    {
        $trueValue = $this->expectValue($this->next());
        $next = $this->next();

        if (!$next) {
            throw new Exception("Ternary expression not properly closed after '?' " . $this->exceptionInfos(), 14);
        }

        if (!$next->is(':')) {
            throw new Exception("':' expected but " . ($next->value ?: $next->type) . ' given ' . $this->exceptionInfos(), 15);
        }

        $next = $this->next();

        if (!$next) {
            throw new Exception("Ternary expression not properly closed after ':' " . $this->exceptionInfos(), 16);
        }

        $falseValue = $this->expectValue($next);
        $this->get(0);

        return new Ternary($condition, $trueValue, $falseValue);
    }

    protected function jsonMethodToPhpFunction($method)
    {
        $function = null;

        switch ($method) {
            case 'stringify':
                $function = 'json_encode';
                break;
            case 'parse':
                $function = 'json_decode';
                break;
        }

        return $function;
    }

    protected function parseJsonMethod($method)
    {
        if ($method->type === 'variable' && ($function = $this->jsonMethodToPhpFunction($method->value))) {
            $this->skip(2);

            if (($next = $this->get(0)) && $next->is('(')) {
                $this->skip();

                return $this->parseFunctionCallChildren($this->parseVariable($function));
            }

            return new Constant('string', var_export($function, true));
        }

        return false;
    }

    protected function parseValue($token)
    {
        if (
            $token->type === 'constant' &&
            $token->value === 'JSON' &&
            ($next = $this->get(0)) &&
            $next->is('.') &&
            ($method = $this->parseJsonMethod($this->get(1))) !== false
        ) {
            return $method;
        }

        return $token->type === 'variable'
            ? $this->parseVariable($token->value)
            : new Constant($token->type, $token->value);
    }

    protected function parseFunction()
    {
        $function = new Block('function');
        $function->enableMultipleInstructions();
        $token = $this->get(0);

        if ($token && $token->type === 'variable') {
            $this->skip();
            $token = $this->get(0);
        }

        if ($token && !$token->is('(')) {
            throw $this->unexpected($token);
        }

        $this->skip();
        $function->setValue($this->parseParentheses());
        $token = $this->get(0);

        if ($token && !$token->is('{')) {
            throw $this->unexpected($token);
        }

        $this->skip();
        $this->parseBlock($function);

        return $function;
    }

    protected function parseKeywordStatement($token)
    {
        $name = $token->value;
        $keyword = new Block($name);

        switch ($name) {
            case 'typeof':
                // @codeCoverageIgnoreStart
                throw new Exception('typeof keyword not supported', 26);
                // @codeCoverageIgnoreEnd
                break;
            case 'new':
            case 'clone':
            case 'return':
            case 'continue':
            case 'break':
                $expects = [
                    'new' => 'Class name',
                    'clone' => 'Object',
                ];
                $value = $this->get(0);

                if (isset($expects[$name]) && !$value) {
                    throw new Exception($expects[$name] . " expected after '" . $name . "'", 25);
                }

                $this->handleOptionalValue($keyword, $value, $name);
                break;
            case 'case':
                $value = $this->expectValue($this->next());
                $keyword->setValue($value);
                $this->expectColon("'case' must be followed by a value and a colon.", 21);
                break;
            case 'default':
                $this->expectColon("'default' must be followed by a colon.", 22);
                break;
            default:
                $this->handleParentheses($keyword, $this->get(0));
        }

        return $keyword;
    }

    protected function parseKeyword($token)
    {
        $keyword = $this->parseKeywordStatement($token);

        if ($keyword->handleInstructions()) {
            $this->parseBlock($keyword);
        }

        return $keyword;
    }

    protected function parseLet()
    {
        $letVariable = $this->get(0);

        if ($letVariable->type !== 'variable') {
            throw $this->unexpected($letVariable);
        }

        return $letVariable->value;
    }

    protected function parseInstruction($block, $token, &$initNext)
    {
        if ($token->type === 'keyword') {
            if ($token->isIn('var', 'const')) {
                $initNext = true;

                return true;
            }

            if ($token->value === 'let') {
                $initNext = true;
                $block->let($this->parseLet());

                return true;
            }
        }

        if ($instruction = $this->getInstructionFromToken($token)) {
            if ($initNext && $instruction instanceof Variable) {
                $instruction = new Assignation('=', $instruction, new Constant('constant', 'null'));
            }

            $initNext = false;
            $block->addInstruction($instruction);

            return true;
        }

        if ($token->is(';') || !$this->engine->getOption('strict')) {
            $initNext = false;
            $block->endInstruction();

            return true;
        }

        return false;
    }

    protected function parseInstructions($block)
    {
        $initNext = false;

        while ($token = $this->next()) {
            if ($token->is($block->multipleInstructions ? '}' : ';')) {
                break;
            }

            if ($this->parseInstruction($block, $token, $initNext)) {
                continue;
            }

            throw $this->unexpected($token);
        }
    }

    /**
     * @param Block $block
     *
     * @throws \JsPhpize\Lexer\Exception
     */
    public function parseBlock($block)
    {
        $this->stack[] = $block;

        if (!$block->multipleInstructions) {
            $next = $this->get(0);

            if ($next && $next->is('{')) {
                $block->enableMultipleInstructions();
            }

            if ($block->multipleInstructions) {
                $this->skip();
            }
        }

        $this->parseInstructions($block);
        array_pop($this->stack);
    }

    public function parse()
    {
        $block = new Main();
        $this->stack = [];
        $this->parseBlock($block);

        return $block;
    }
}
