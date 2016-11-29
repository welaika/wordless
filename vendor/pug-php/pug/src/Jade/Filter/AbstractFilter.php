<?php

namespace Jade\Filter;

use Jade\Compiler;
use Jade\Nodes\Filter;

/**
 * Class Jade\Filter\AbstractFilter.
 */
abstract class AbstractFilter implements FilterInterface
{
    /**
     * Returns the node string value, line by line.
     * If the compiler is present, that means we need
     * to interpolate line contents.
     *
     * @param Filter   $node
     * @param Compiler $compiler
     *
     * @return mixed
     */
    protected function getNodeString(Filter $node, Compiler $compiler = null)
    {
        return array_reduce($node->block->nodes, function ($result, $line) use ($compiler) {
            return $result . ($compiler
                ? $compiler->interpolate($line->value)
                : $line->value
            ) . "\n";
        });
    }

    public function __invoke(Filter $node, Compiler $compiler)
    {
        $nodes = $node->block->nodes;
        $indent = strlen($nodes[0]->value) - strlen(ltrim($nodes[0]->value));
        $code = '';
        foreach ($nodes as $line) {
            $code .= substr($compiler->interpolate($line->value), $indent) . "\n";
        }

        if (method_exists($this, 'parse')) {
            $code = $this->parse($code);
        }

        if (isset($this->tag)) {
            $code = '<' . $this->tag . (isset($this->textType) ? ' type="text/' . $this->textType . '"' : '') . '>' .
                $code .
                '</' . $this->tag . '>';
        }

        return $code;
    }
}
