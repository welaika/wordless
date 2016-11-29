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
            if (preg_match('/^ *\((.*?)\)/', $this->input, $matchesArguments)) {
                $this->consume($matchesArguments[0]);
                $token->arguments = $matchesArguments[1];
            }

            return $token;
        }
    }

    /**
     * @return object
     */
    protected function scanMixin()
    {
        if (preg_match('/^mixin +(\w[-\w]*)(?: *\((.*)\))?/', $this->input, $matches)) {
            $this->consume($matches[0]);
            $token = $this->token('mixin', $matches[1]);
            $token->arguments = isset($matches[2]) ? $matches[2] : null;

            return $token;
        }
    }
}
