<?php

namespace Jade\Lexer;

/**
 * Class Jade\Lexer\MixinScanner.
 */
abstract class MixinScanner extends CaseScanner
{
    /**
     * @return object
     */
    protected function scanCall()
    {
        if (preg_match('/^\+(\w[-\w]*)/', $this->input, $matches)) {
            $this->consume($matches[0]);
            $token = $this->token('call', $matches[1]);

            // check for arguments
            if (preg_match('/^ *' . Scanner::PARENTHESES . '/', $this->input, $matchesArguments)) {
                $this->consume($matchesArguments[0]);
                $token->arguments = trim(substr($matchesArguments[1], 1, -1));
            }

            return $token;
        }
    }

    /**
     * @return object
     */
    protected function scanMixin()
    {
        if (preg_match('/^mixin +(\w[-\w]*)(?: *' . Scanner::PARENTHESES . ')?/', $this->input, $matches)) {
            $this->consume($matches[0]);
            $token = $this->token('mixin', $matches[1]);
            $token->arguments = isset($matches[2]) ? trim(substr($matches[2], 1, -1)) : null;

            return $token;
        }
    }
}
