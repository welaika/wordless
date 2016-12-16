<?php

namespace Jade\Compiler;

use Jade\Nodes\CaseNode;
use Jade\Nodes\CustomKeyword;
use Jade\Nodes\Each;
use Jade\Nodes\Filter;
use Jade\Nodes\When;

abstract class KeywordsCompiler extends AttributesCompiler
{
    /**
     * @param Nodes\CaseNode $node
     */
    protected function visitCasenode(CaseNode $node)
    {
        $this->switchNode = $node;
        $this->visit($node->block);

        if (!isset($this->switchNode)) {
            unset($this->switchNode);
            $this->indents--;

            $code = $this->createCode('}');
            $this->buffer($code);
        }
    }

    /**
     * @param string $expression
     * @param &array $arguments
     *
     * @return string
     */
    protected function visitCase($expression, &$arguments)
    {
        if ('default' === $expression) {
            return 'default:';
        }

        $arguments[] = $expression;

        return 'case %s:';
    }

    /**
     * @param Nodes\When $node
     */
    protected function visitWhen(When $node)
    {
        $code = '';
        $arguments = array();

        if (isset($this->switchNode)) {
            $code .= 'switch (%s) {';
            $arguments[] = $this->switchNode->expr;
            unset($this->switchNode);

            $this->indents++;
        }

        $code .= $this->visitCase($node->expr, $arguments);

        array_unshift($arguments, $code);

        $code = call_user_func_array(array($this, 'createCode'), $arguments);

        $this->buffer($code);

        $this->visit($node->block);

        $code = $this->createCode('break;');
        $this->buffer($code . $this->newline());
    }

    /**
     * @param Nodes\Filter $node
     *
     * @throws \InvalidArgumentException
     */
    protected function visitFilter(Filter $node)
    {
        $filter = $this->getFilter($node->name);

        // Filters can be either a iFilter implementation, nor a callable
        if (is_string($filter) && class_exists($filter)) {
            $filter = new $filter();
        }
        if (!is_callable($filter)) {
            throw new \InvalidArgumentException($node->name . ': Filter must be callable', 18);
        }
        $this->buffer($filter($node, $this));
    }

    /**
     * @param Nodes\Each $node
     */
    protected function visitEach(Each $node)
    {
        //if (is_numeric($node->obj)) {
        //if (is_string($node->obj)) {
        //$serialized = serialize($node->obj);
        if (isset($node->alternative)) {
            $this->buffer($this->createCode(
                'if (isset(%s) && %s) {',
                $node->obj, $node->obj
            ));
            $this->indents++;
        }

        $this->buffer(isset($node->key) && strlen($node->key) > 0
            ? $this->createCode(
                'foreach (%s as %s => %s) {',
                $node->obj, $node->key, $node->value
            )
            : $this->createCode(
                'foreach (%s as %s) {',
                $node->obj, $node->value
            )
        );

        $this->indents++;
        $this->visit($node->block);
        $this->indents--;

        $this->buffer($this->createCode('}'));

        if (isset($node->alternative)) {
            $this->indents--;
            $this->buffer($this->createCode('} else {'));
            $this->indents++;

            $this->visit($node->alternative);
            $this->indents--;

            $this->buffer($this->createCode('}'));
        }
    }

    protected function bufferCustomKeyword($data, $block)
    {
        if (isset($data['begin'])) {
            $this->buffer($data['begin']);
        }

        if ($block) {
            $this->indents++;
            $this->visit($block);
            $this->indents--;
        }

        if (isset($data['end'])) {
            $this->buffer($data['end']);
        }
    }

    /**
     * @param Nodes\CustomKeyword $node
     */
    protected function visitCustomKeyword(CustomKeyword $node)
    {
        $action = $this->options['customKeywords'][$node->keyWord];

        $data = $action($node->args, $node->block, $node->keyWord);

        if (is_string($data)) {
            $data = array(
                'begin' => $data,
            );
        }

        if (!is_array($data) && !($data instanceof \ArrayAccess)) {
            throw new \ErrorException("The keyword {$node->keyWord} returned an invalid value type, string or array was expected.", 33);
        }

        foreach (array('begin', 'end') as $key) {
            $data[$key] = (isset($data[$key . 'Php'])
                ? $this->createCode($data[$key . 'Php'])
                : ''
            ) . (isset($data[$key])
                ? $data[$key]
                : ''
            );
        }

        $this->bufferCustomKeyword($data, $node->block);
    }
}
