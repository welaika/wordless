<?php

namespace JsPhpize\Compiler;

use JsPhpize\JsPhpize;
use JsPhpize\Nodes\Assignation;
use JsPhpize\Nodes\Block;
use JsPhpize\Nodes\BracketsArray;
use JsPhpize\Nodes\Constant;
use JsPhpize\Nodes\Dyiade;
use JsPhpize\Nodes\DynamicValue;
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
    const DOT_DISABLED = 1;

    use DyiadeTrait;
    use InterpolationTrait;

    /**
     * @const string
     */
    const STATIC_CALL_FUNCTIONS = 'array,echo,print,printf,exit,__halt_compiler,abstract,and,array,as,break,
        callable,case,catch,class,clone,const,continue,declare,default,die,do,echo,else,elseif,empty,enddeclare,
        endfor,endforeach,endif,endswitch,endwhile,eval,exit,extends,final,for,foreach,function,global,goto,if,
        implements,include,include_once,instanceof,insteadof,interface,isset,list,namespace,new,or,print,private,
        protected,public,require,require_once,return,static,switch,throw,trait,try,typeof,unset,use,var,while,
        xor';

    /**
     * @var JsPhpize
     */
    protected $engine;

    /**
     * @var bool
     */
    protected $arrayShortSyntax;

    /**
     * @var string
     */
    protected $filename;

    public function __construct(JsPhpize $engine, $filename = null)
    {
        $this->engine = $engine;
        $this->filename = $filename;
        $this->setPrefixes($engine->getVarPrefix(), $engine->getConstPrefix());
        $this->arrayShortSyntax = $engine->getOption('arrayShortSyntax', false);
    }

    protected function arrayWrap($arrayBody)
    {
        return sprintf($this->arrayShortSyntax ? '[ %s ]' : 'array( %s )', $arrayBody);
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
                $rightHand = $this->helperWrap($set, [
                    $this->visitNode($assignation->leftHand, $indent),
                    $this->visitNode($lastChild, $indent),
                    var_export($assignation->operator, true),
                    $rightHand,
                ]);
            }
        }

        return $this->visitNode($assignation->leftHand, $indent) .
            ' ' . $assignation->operator .
            ' ' . $rightHand;
    }

    protected function getBlockHead(Block $block, $indent)
    {
        if ($block->type === 'for' && $block->value instanceof Parenthesis && $block->value->separator === 'in' && count($block->value->nodes) >= 2) {
            return 'foreach (' .
                $this->visitNode($block->value->nodes[1], $indent) .
                ' as ' . $this->visitNode($block->value->nodes[0], $indent) .
                ' => $__current_value)';
        }

        return $block->type . (
            $block->value
            ? ' ' . $this->visitNode($block->value, $indent)
            : ''
        );
    }

    protected function visitBlock(Block $block, $indent)
    {
        $head = $this->getBlockHead($block, $indent);

        if (!$block->handleInstructions()) {
            return $head;
        }

        if ($block->type === 'function' && count($readVariables = $block->getReadVariables())) {
            $readVariables = array_map('strval', $readVariables);
            $head .= ' use (&$' . implode(', &$', array_unique($readVariables)) . ')';
        }

        $letVariables = $this->visitNodesArray($block->getLetVariables(), $indent, '', $indent . "unset(%s);\n");

        return $head . " {\n" .
            $this->compile($block, '  ' . $indent) .
            $letVariables .
            $indent . '}';
    }

    protected function visitBracketsArray(BracketsArray $array, $indent)
    {
        $visitNode = [$this, 'visitNode'];

        return $this->arrayWrap(implode(', ', array_map(
            function ($pair) use ($visitNode, $indent) {
                list($key, $value) = $pair;

                return $visitNode($key, $indent) .
                    ' => ' .
                    $visitNode($value, $indent);
            },
            $array->data
        )));
    }

    protected function visitConstant(Constant $constant)
    {
        $value = $constant->value;

        if ($constant->type === 'string') {
            if (substr($value, 0, 1) === '`') {
                return implode(
                    ' . ',
                    iterator_to_array($this->readInterpolation(substr($value, 1, -1)))
                );
            }

            if (mb_substr($constant->value, 0, 1) === '"') {
                return str_replace('$', '\\$', $value);
            }
        }

        if ($constant->type === 'regexp') {
            return $this->helperWrap(
                $this->engine->getHelperName('regExp'),
                [var_export($value, true)]
            );
        }

        return $value;
    }

    protected function visitDyiade(Dyiade $dyiade, $indent)
    {
        $leftHand = $this->visitNode($dyiade->leftHand, $indent);
        $rightHand = $this->visitNode($dyiade->rightHand, $indent);
        switch ($dyiade->operator) {
            case '||':
                if ($this->engine->getOption('booleanLogicalOperators')) {
                    break;
                }

                return $this->compileLazyDyiade($this->engine->getHelperName('or'), $leftHand, $rightHand);
            case '&&':
                if ($this->engine->getOption('booleanLogicalOperators')) {
                    break;
                }

                return $this->compileLazyDyiade($this->engine->getHelperName('and'), $leftHand, $rightHand);
            case '+':
                $arguments = [$leftHand, $rightHand];
                while (
                    ($dyiade = $dyiade->rightHand) instanceof Dyiade &&
                    $dyiade->operator === '+'
                ) {
                    /* @var Dyiade $dyiade */
                    array_pop($arguments);
                    $arguments[] = $this->visitNode($dyiade->leftHand, $indent);
                    $arguments[] = $this->visitNode($dyiade->rightHand, $indent);
                }

                return $this->helperWrap($this->engine->getHelperName('plus'), $arguments);
        }

        return $leftHand . ' ' . $dyiade->operator . ' ' . $rightHand;
    }

    protected function mapNodesArray($array, $indent, $pattern = null, $options = 0)
    {
        $visitNode = [$this, 'visitNode'];

        return array_map(function ($value) use ($visitNode, $indent, $pattern, $options) {
            $value = $visitNode($value, $indent, $options);

            if ($pattern) {
                $value = sprintf($pattern, $value);
            }

            return $value;
        }, $array);
    }

    protected function visitNodesArray($array, $indent, $glue = '', $pattern = null, $options = 0)
    {
        return implode($glue, $this->mapNodesArray($array, $indent, $pattern, $options));
    }

    protected function visitFunctionCall(FunctionCall $functionCall, $indent)
    {
        $function = $functionCall->function;
        $arguments = $functionCall->arguments;
        $applicant = $functionCall->applicant;
        $arguments = $this->visitNodesArray(
            $arguments,
            $indent,
            ', ',
            null,
            $function instanceof Variable && $function->name === 'isset' ? static::DOT_DISABLED : 0
        );
        $dynamicCall = $this->visitNode($function, $indent) . '(' . $arguments . ')';

        if ($function instanceof Variable && count($function->children) === 0) {
            $name = $function->name;
            $staticCall = $name . '(' . $arguments . ')';

            $functions = str_replace(["\n", "\t", "\r", ' '], '', static::STATIC_CALL_FUNCTIONS);

            if ($applicant === 'new' || in_array($name, explode(',', $functions))) {
                return $staticCall;
            }

            $dynamicCall = '(function_exists(' . var_export($name, true) . ') ? ' .
                $staticCall . ' : ' .
                $dynamicCall . ')';

            $functionNamespace = $this->engine->getOption('functionsNamespace');

            if ($functionNamespace) {
                $dynamicCall = '(function_exists(' . var_export($functionNamespace . '\\' . $name, true) . ') ? ' .
                    '\\' . $functionNamespace . '\\' . $staticCall . ' : ' .
                    $dynamicCall . ')';
            }
        }

        return $this->handleVariableChildren($functionCall, $indent, $dynamicCall);
    }

    protected function visitHooksArray(HooksArray $array, $indent)
    {
        return $this->arrayWrap($this->visitNodesArray($array->data, $indent, ', '));
    }

    protected function visitInstruction(Instruction $group, $indent)
    {
        $visitNode = [$this, 'visitNode'];
        $isReturnPrepended = $group->isReturnPrepended();

        return implode('', array_map(function ($instruction) use ($visitNode, $indent, $isReturnPrepended) {
            $value = $visitNode($instruction, $indent);

            return $indent .
                (
                    $instruction instanceof Block && $instruction->handleInstructions()
                    ? $value
                    : (
                        $isReturnPrepended && !preg_match('/^\s*return(?![a-zA-Z0-9_])/', $value)
                        ? ' return '
                        : ''
                    ) . $value . ';'
                ) .
                "\n";
        }, $group->instructions));
    }

    public function visitNode(Node $node, $indent, $options = 0)
    {
        $method = preg_replace(
            '/^(.+\\\\)?([^\\\\]+)$/',
            'visit$2',
            get_class($node)
        );
        $php = method_exists($this, $method) ? $this->$method($node, $indent, $options) : '';

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

    protected function handleVariableChildren(DynamicValue $dynamicValue, $indent, $php, $options = 0)
    {
        $children = $dynamicValue->children;

        if (count($children)) {
            return $this->wrapVariableChildren($children, $indent, $php, $options);
        }

        return $php;
    }

    protected function wrapVariableChildren($children, $indent, $php, $options)
    {
        $arguments = $this->mapNodesArray($children, $indent);
        array_unshift($arguments, $php);
        $dot = $this->engine->getHelperName('dot');
        $dotDisabled = $options & static::DOT_DISABLED;

        if ($dotDisabled) {
            $lastChild = end($children);
            $dotChild = $lastChild instanceof Constant && $lastChild->dotChild;
            $lastChild = array_pop($arguments);
        }

        $php = $this->helperWrap($dot, $arguments);

        if ($dotDisabled) {
            $pattern = $dotChild ? '%s->{%s}' : '%s[%s]';
            $php = sprintf($pattern, $php, $lastChild);
        }

        return $php;
    }

    protected function visitVariable(Variable $variable, $indent, $options = 0)
    {
        $name = $variable->name;
        if (in_array($name, ['Math', 'RegExp'])) {
            $this->requireHelper(lcfirst($name) . 'Class');
        }
        if ($variable->scope) {
            $name = '__let_' . spl_object_hash($variable->scope) . $name;
        }
        if (!$this->engine->getOption('ignoreDollarVariable') || mb_substr($name, 0, 1) !== '$') {
            $name = '$' . $name;
        }

        return $this->handleVariableChildren($variable, $indent, $name, $options);
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
