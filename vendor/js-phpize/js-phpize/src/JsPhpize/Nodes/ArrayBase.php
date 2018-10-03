<?php

namespace JsPhpize\Nodes;

/**
 * Class ArrayBase.
 *
 * @property-read array $data array elements
 */
abstract class ArrayBase extends Value
{
    protected $data = [];
}
