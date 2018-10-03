<?php

namespace JsPhpize\Nodes;

/**
 * Class DynamicValue.
 *
 * @property-read array $children List of sub-variables (properties of object/values of array)
 */
abstract class DynamicValue extends Value
{
    /**
     * @var array
     */
    protected $children;

    protected function mergeVariables(array $variables, array $nodes)
    {
        foreach ($nodes as $node) {
            $variables = array_merge($variables, $node->getReadVariables());
        }

        return $variables;
    }
}
