<?php

namespace Jade\Nodes;

abstract class Node
{
    public function isInline()
    {
        if (isset($this->isBlock) && $this->isBlock) {
            foreach ($this->nodes as $node) {
                if (!$node->isInline()) {
                    return false;
                }
            }

            return true;
        }

        return isset($this->isText) && $this->isText;
    }
}
