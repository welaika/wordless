<?php

namespace JsPhpize\Nodes;

interface Assignable
{
    public function getNonAssignableReason();

    public function isAssignable();
}
