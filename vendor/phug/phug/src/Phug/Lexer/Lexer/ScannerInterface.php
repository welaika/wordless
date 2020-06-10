<?php

namespace Phug\Lexer;

interface ScannerInterface
{
    public function scan(State $state);
}
