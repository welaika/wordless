<?php

namespace Phug\Parser;

use Phug\Lexer\TokenInterface;

interface TokenHandlerInterface
{
    public function handleToken(TokenInterface $token, State $state);
}
