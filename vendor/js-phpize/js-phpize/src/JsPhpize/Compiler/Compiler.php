<?php

namespace JsPhpize\Compiler;

use JsPhpize\JsPhpize;
use JsPhpize\Nodes\Assignation;
use JsPhpize\Nodes\Block;
use JsPhpize\Nodes\BracketsArray;
use JsPhpize\Nodes\Constant;
use JsPhpize\Nodes\Dyiade;
use JsPhpize\Nodes\FunctionCall;
use JsPhpize\Nodes\HooksArray;
use JsPhpize\Nodes\Instruction;
use JsPhpize\Nodes\Node;
use JsPhpize\Nodes\Parenthesis;
use JsPhpize\Nodes\Ternary;
use JsPhpize\Nodes\Value;
use JsPhpize\Nodes\Variable;

class Compiler
{
    /**
     * @const string
     */
    const STATIC_CALL_FUNCTIONS = 'array,echo,print,printf,exit,__halt_compiler,abstract,and,array,as,break,
        callable,case,catch,class,clone,const,continue,declare,default,die,do,echo,else,elseif,empty,enddeclare,
        endfor,endforeach,endif,endswitch,endwhile,eval,exit,extends,final,for,foreach,function,global,goto,if,
        implements,include,include_once,instanceof,insteadof,interface,isset,list,namespace,new,or,print,private,
        protected,public,require,require_once,return,static,switch,throw,trait,try,unset,use,var,while,xor';

    /**
     * @var JsPhpize
     */
    protected $engine;

    /**
     * @var string
     */
    protected $varPrefix;

    /**
     * @var string
     */
    protected $constPrefix;

    /**
     * @var bool
     */
    protected $arrayShortSyntax;

    /**
     * @var array
     */
    protected $helpers = array();

    public function __construct(JsPhpize $engine)
    {
        $this->engine = $engine;
        $this->varPrefix = $engine->getVarPrefix();
        $this->constPrefix = $engine->getConstPrefix();
        $this->arrayShortSyntax = $engine->getOption('arrayShortSyntax', false);
    }

    protected function helperWrap($helper, $arguments)
    {
        $this->helpers[$helper] = true;

        return 'call_user_func(' .
            '$GLOBALS[\'' . $this->varPrefix . $helper . '\']' .
            implode('', array_map(function ($argument) {
                return ', ' . $argument;
            }, $arguments)) .
        ')';
    }

    protected function arrayWrap($arrayBody)
    {
        return sprintf($this->arrayShortSyntax ? '[ %s ]' : 'array( %s )', $arrayBody);
    }

    public function getDependencies()
    {
        return array_keys($this->helpers);
    }

    public function compileDependencies($dependencies)
    {
        $php = '';

        foreach ($dependencies as $name) {
            $file = preg_match('/^[a-z0-9_-]+$/i', $name)
                ? __DIR__ . '/Helpers/' . ucfirst($name) . '.h'
                : $name;

            $code = file_exists($file)
                ? file_get_contents($file)
                : $name;

            $php .= '$GLOBALS[\'' . $this->varPrefix . $name . '\'] = ' .
                trim($code) .
                ";\n";
        }

        return $php;
    }

    protected function visitAssignation(Assignation $assignation, $indent)
    {
        if ($assignation->leftHand instanceof Constant && $assignation->leftHand->type === 'constant') {
            return 'define(' .
                var_export(strval($assignation->leftHand->value), true) . ', ' .
                $this->visitNode($assignation->rightHand, $indent) .
            ')';
        }

        $rightHand = $this->visitNode($assignation->rightHand, $indent);

        if ($assignation->leftHand instanceof Variable && count($assignation->leftHand->children)) {
            $set = $this->engine->getHelperName('set');

            while ($lastChild = $assignation->leftHand->popChild()) {
                $rightHand = $this->helperWrap($set, array(
                    $this->visitNode($assignation->leftHand, $indent),
                    $this->visitNode($lastChild, $indent),
                    var_export($assignation->operator, true),
                    $rightHand,
                ));
            }
        }

        return $this->visitNode($assignation->leftHand, $indent) .
            ' ' . $assignation->operator .
            ' ' . $rightHand;
    }

    protected function visitBlock(Block $block, $indent)
    {
        $head = $block->type . ($block->value
            ? ' ' . $this->visitNode($block->value, $indent)
            : ''
        );

        if (!$block->handleInstructions()) {
            return $head;
        }

        if ($block->type === 'function' && count($readVariables = $block->getReadVariables())) {
            $head .= ' use (&$' . implode(', &$', $readVariables) . ')';
        }

        $letVariables = $this->visitNodesArray($block->getLetVariables(), $indent, '', $indent . "unset(%s);\n");

        return $head . " {\n" .
            $this->compile($block, '  ' . $indent) .
            $letVariables .
            $indent . '}';
    }

    protected function visitBracketsArray(BracketsArray $array, $indent)
    {
        $visitNode = array($this, 'visitNode');

        return $this->arrayWrap(implode(', ', array_map(
            function ($pair) use ($visitNode, $indent) {
                list($key, $value) = $pair;

                return call_user_func($visitNode, $key, $indent) .
                    ' => ' .
                    call_user_func($visitNode, $value, $indent);
            },
            $array->data
        )));
    }

    protected function visitConstant(Constant $constant)
    {
        $value = $constant->value;
        if ($constant->type === 'string' && mb_substr($constant->value, 0, 1) === '"') {
            $value = str_replace('$', '\\$', $value);
        }
        if ($constant->type === 'regexp') {
            $regExp = $this->engine->getHelperName('regExp');
            $value = $this->helperWrap($regExp, array(var_export($value, true)));
        }

        return $value;
    }

    protected function visitDyiade(Dyiade $dyiade, $indent)
    {
        $leftHand = $this->visitNode($dyiade->leftHand, $indent);
        $rightHand = $this->visitNode($dyiade->rightHand, $indent);
        if ($dyiade->operator === '+') {
            $arguments = array($leftHand, $rightHand);
            while (
                ($dyiade = $dyiade->rightHand) instanceof Dyiade &&
                $dyiade->operator === '+'
            ) {
                array_pop($arguments);
                $arguments[] = $this->visitNode($dyiade->leftHand, $indent);
                $arguments[] = $this->visitNode($dyiade->rightHand, $indent);
            }

            $plus = $this->engine->getHelperName('plus');

            return $this->helperWrap($plus, $arguments);
        }

        return $leftHand . ' ' . $dyiade->operator . ' ' . $rightHand;
    }

    protected function mapNodesArray($array, $indent, $pattern = null)
    {
        $visitNode = array($this, 'visitNode');

        return array_map(function ($value) use ($visitNode, $indent, $pattern) {
            $value = call_user_func($visitNode, $value, $indent);

            if ($pattern) {
                $value = sprintf($pattern, $value);
            }

            return $value;
        }, $array);
    }

    protected function visitNodesArray($array, $indent, $glue = '', $pattern = null)
    {
        return implode($glue, $this->mapNodesArray($array, $indent, $pattern));
    }

    protected function visitFunctionCall(FunctionCall $functionCall, $indent)
    {
        $function = $functionCall->function;
        $arguments = $functionCall->arguments;
        $applicant = $functionCall->applicant;
        $arguments = $this->visitNodesArray($arguments, $indent, ', ');
        $dynamicCall = 'call_user_func(' .
            $this->visitNode($function, $indent) .
            ($arguments === '' ? '' : ', ' . $arguments) .
        ')';

        if ($function instanceof Variable && count($function->children) === 0) {
            $name = $function->name;
            $staticCall = $name . '(' . $arguments . ')';

            $functions = str_replace(array("\n", "\t", "\r", ' '), '', static::STATIC_CALL_FUNCTIONS);
            if ($applicant === 'new' || in_array($name, explode(',', $functions))) {
                return $staticCall;
            }

            return '(function_exists(' . var_export($name, true) . ') ? ' .
                $staticCall . ' : ' .
                $dynamicCall . ')';
        }

        if (count($functionCall->children)) {
            $arguments = $this->mapNodesArray($functionCall->children, $indent);
            array_unshift($arguments, $dynamicCall);
            $dot = $this->engine->getHelperName('dot');
            $dynamicCall = $this->helperWrap($dot, $arguments);
        }

        return $dynamicCall;
    }

    protected function visitHooksArray(HooksArray $array, $indent)
    {
        return $this->arrayWrap($this->visitNodesArray($array->data, $indent, ', '));
    }

    protected function visitInstruction(Instruction $group, $indent)
    {
        $visitNode = array($this, 'visitNode');
        $isReturnPrepended = $group->isReturnPrepended();

        return implode('', array_map(function ($instruction) use ($visitNode, $indent, $isReturnPrepended) {
            $value = call_user_func($visitNode, $instruction, $indent);

            return $indent .
                ($instruction instanceof Block && $instruction->handleInstructions()
                    ? $value
                    : ($isReturnPrepended && !preg_match('/^\s*return(?![a-zA-Z0-9_])/', $value)
                        ? ' return '
                        : ''
                    ) . $value . ';'
                ) .
                "\n";
        }, $group->instructions));
    }

    public function visitNode(Node $node, $indent)
    {
        $method = preg_replace(
            '/^(.+\\\\)?([^\\\\]+)$/',
            'visit$2',
            get_class($node)
        );
        $php = method_exists($this, $method) ? $this->$method($node, $indent) : '';
        if ($node instanceof Value) {
            $php = $node->getBefore() . $php . $node->getAfter();
        }

        return $php;
    }

    protected function visitParenthesis(Parenthesis $parenthesis, $indent)
    {
        return '(' . $this->visitNodesArray($parenthesis->nodes, $indent, $parenthesis->separator . ' ') . ')';
    }

    protected function visitTernary(Ternary $ternary, $indent)
    {
        return $this->visitNode($ternary->condition, $indent) .
            ' ? ' . $this->visitNode($ternary->trueValue, $indent) .
            ' : ' . $this->visitNode($ternary->falseValue, $indent);
    }

    protected function visitVariable(Variable $variable, $indent)
    {
        $name = $variable->name;
        if ($variable->scope) {
            $name = '__let_' . spl_object_hash($variable->scope) . $name;
        }
        $php = '$' . $name;
        if (count($variable->children)) {
            $arguments = $this->mapNodesArray($variable->children, $indent);
            array_unshift($arguments, $php);
            $dot = $this->engine->getHelperName('dot');
            $php = $this->helperWrap($dot, $arguments);
        }

        return $php;
    }

    public function compile(Block $block, $indent = '')
    {
        $output = '';

        $count = count($block->instructions);
        foreach ($block->instructions as $index => $instruction) {
            if ($index === $count - 1 && $this->engine->getOption('returnLastStatement')) {
                $instruction->prependReturn();
            }
            $output .= $this->visitNode($instruction, $indent);
        }

        return $output;
    }
}
