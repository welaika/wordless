<?php

namespace Phug\Lexer;

interface HandleTokenInterface
{
    public function isHandled();

    public function markAsHandled();
}
