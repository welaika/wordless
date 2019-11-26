<?php

namespace Phug\Compiler\NodeCompiler;

use Phug\CompilerInterface;
use Phug\Formatter\Element\CodeElement;
use Phug\Formatter\ElementInterface;
use Phug\Parser\Node\CommentNode;
use Phug\Parser\Node\ConditionalNode;
use Phug\Parser\Node\EachNode;
use Phug\Parser\NodeInterface;

class EachNodeCompiler extends AbstractStatementNodeCompiler
{
    const EACH_SCOPE_VARIABLE_NAME = '__eachScopeVariables';

    protected function getScopeVariablesDump($eachScopeVariableName, array $variables)
    {
        $dump = [];

        foreach ($variables as $name) {
            $dump[] = "'$name' => isset(\$$name) ? \$$name : null";
        }

        return '$'.$eachScopeVariableName.' = ['.implode(', ', $dump).'];';
    }

    protected function getScopeVariablesRestore($eachScopeVariableName)
    {
        return 'extract($'.$eachScopeVariableName.');';
    }

    protected function scopeEachVariables(CompilerInterface $compiler, CodeElement $loop, array $variables)
    {
        $eachScopeVariableName = $compiler->getOption('scope_each_variables');

        if ($eachScopeVariableName === true) {
            $eachScopeVariableName = static::EACH_SCOPE_VARIABLE_NAME;
        }

        if ($eachScopeVariableName) {
            $loop->setPreHook($this->getScopeVariablesDump($eachScopeVariableName, $variables));
            $loop->setPostHook($this->getScopeVariablesRestore($eachScopeVariableName));
        }
    }

    protected function compileLoop(NodeInterface $node, $items, $key, $item)
    {
        $variables = [$item];
        $compiler = $this->getCompiler();
        $subject = $compiler->getFormatter()->formatCode($items).' as ';

        if ($key) {
            $variables[] = $key;
            $subject .= '$'.$key.' => ';
        }

        $subject .= '$'.$item;

        /** @var CodeElement $loop */
        $loop = $this->wrapStatement($node, 'foreach', $subject);
        $this->scopeEachVariables($compiler, $loop, $variables);
        $next = $node->getNextSibling();

        while ($next && $next instanceof CommentNode) {
            $next = $next->getNextSibling();
        }

        if ($next instanceof ConditionalNode && $next->getName() === 'else') {
            $next->setName('if');
            $next->setSubject('$__pug_temp_empty');
            $loop->setValue('$__pug_temp_empty = true; '.$loop->getValue());
            $loop->prependChild(new CodeElement('$__pug_temp_empty = false', $next));
        }

        return $loop;
    }

    public function compileNode(NodeInterface $node, ElementInterface $parent = null)
    {
        $this->getCompiler()->assert(
            $node instanceof EachNode,
            'Unexpected '.get_class($node).' given to each compiler.',
            $node
        );

        /** @var EachNode $node */
        $subject = $node->getSubject();
        $key = $node->getKey();
        $item = $node->getItem();

        return $this->compileLoop($node, $subject, $key, $item);
    }
}
