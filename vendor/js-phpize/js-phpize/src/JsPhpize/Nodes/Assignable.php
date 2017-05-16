<?php

namespace JsPhpize\Nodes;

interface Assignable
{
    /**
     * Returns false if assignable or the reason it's not as a string.
     *
     * @return false|string
     */
    public function getNonAssignableReason();
}
