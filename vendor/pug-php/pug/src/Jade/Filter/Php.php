<?php

namespace Jade\Filter;

use Jade\Compiler;
use Jade\Nodes\Filter;

/**
 * Class Jade\Filter\Php.
 */
class Php implements FilterInterface
{
    /**
     * @param Filter   $node
     * @param Compiler $compiler
     *
     * @return string
     */
    public function __invoke(Filter $node, Compiler $compiler)
    {
        $data = '';

        foreach ($node->block->nodes as $n) {
            if (isset($n->value)) {
                $data .= preg_match('/^[[:space:]]*\|(?!\|)(.*)/', $n->value, $m)
                    ? $compiler->wrapOutPhp($m[1])
                    : $n->value . "\n";
                continue;
            }
            $data .= $compiler->wrapOutPhp($compiler->subCompiler()->compile($n));
        }

        return $data ? $compiler->wrapInPhp($data) : $data;
    }
}
