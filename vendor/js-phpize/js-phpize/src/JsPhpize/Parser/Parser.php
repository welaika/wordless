<?php

namespace JsPhpize\Parser;

use JsPhpize\JsPhpize;
use JsPhpize\Lexer\Lexer;
use JsPhpize\Nodes\Block;
use JsPhpize\Nodes\BracketsArray;
use JsPhpize\Nodes\Constant;
use JsPhpize\Nodes\HooksArray;
use JsPhpize\Nodes\Main;
use JsPhpize\Nodes\Node;
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
     * @var Lexer
     */
    protected $lexer;

    /**
     * @var array
     */
    protected $tokens;

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
        $input = str_replace(array("\r\n", "\r"), array("\n", ''), $input);
        $this->tokens = array();
        $this->dependencies = array();
        $this->engine = $engine;
        $this->lexer = new Lexer($engine, $input, $filename);
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

    protected function parseParentheses()
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
                if ($token->isIn(',', ';')) {
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
            throw $this->unexpected($token);
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

    protected function parseVariable($name)
    {
        $children = array();
        while ($next = $this->get(0)) {
            if ($next->type === 'lambda') {
                $this->skip();
                $parenthesis = new Parenthesis();
                $parenthesis->addNode(new Variable($name, $children));

                return $this->parseLambda($parenthesis);
            }

            if ($value = $this->getVariableChildFromToken($next)) {
                $children[] = $value;

                continue;
            }

            break;
        }

        $variable = new Variable($name, $children);

        for ($i = count($this->stack) - 1; $i >= 0; $i--) {
            $block = $this->stack[$i];
            if ($block->isLet($name)) {
                $variable->setScope($block);

                break;
            }
        }

        return $variable;
    }

    protected function parseTernary(Node $condition)
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
        $next = $this->get(0);

        return new Ternary($condition, $trueValue, $falseValue);
    }

    protected function parseValue($token)
    {
        return $token->type === 'variable'
            ? $this->parseVariable($token->value)
            : new Constant($token->type, $token->value);
    }

    protected function parseFunction($token)
    {
        $function = new Block('function');
        $token = $this->get(0);
        if ($token->type === 'variable') {
            $this->skip();
            $token = $this->get(0);
        }
        if (!$token->is('(')) {
            throw $this->unexpected($token);
        }
        $this->skip();
        $function->setValue($this->parseParentheses());
        $token = $this->get(0);
        if (!$token->is('{')) {
            throw $this->unexpected($token);
        }
        $this->skip();
        $this->parseBlock($function);
        $this->skip();

        return $function;
    }

    protected function parseKeywordStatement($token)
    {
        $name = $token->value;
        $keyword = new Block($name);
        switch ($name) {
            case 'return':
            case 'continue':
            case 'break':
                $this->handleOptionalValue($keyword, $this->get(0));
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

    protected function parseLet($token)
    {
        $letVariable = $this->get(0);
        if ($letVariable->type !== 'variable') {
            throw $this->unexpected($letVariable, $token);
        }

        return $letVariable->value;
    }

    protected function parseInstructions($block)
    {
        $endToken = $this->getEndTokenFromBlock($block);
        while ($token = $this->next()) {
            if ($token->is($endToken)) {
                break;
            }
            if ($token->type === 'keyword') {
                if ($token->isIn('var', 'const')) {
                    continue;
                }
                if ($token->value === 'let') {
                    $block->let($this->parseLet($token));
                    continue;
                }
            }
            if ($instruction = $this->getInstructionFromToken($token)) {
                $block->addInstruction($instruction);
                continue;
            }
            if ($token->is(';')) {
                $block->endInstruction();
                continue;
            }
            throw $this->unexpected($token);
        }
    }

    public function parseBlock($block)
    {
        $this->stack[] = $block;
        $next = $this->get(0);
        if ($next && $next->is('(')) {
            $this->skip();
            $block->setValue($this->parseParentheses());
        }
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
        $this->stack = array();
        $this->parseBlock($block);

        return $block;
    }
}
