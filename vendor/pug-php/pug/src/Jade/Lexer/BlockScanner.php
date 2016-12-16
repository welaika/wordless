<?php

namespace Jade\Lexer;

/**
 * Class Jade\Lexer\BlockScanner.
 */
abstract class BlockScanner extends IndentScanner
{
    /**
     * @return object
     */
    protected function scanExtends()
    {
        return $this->scan('/^extends? +([^\n]+)/', 'extends');
    }

    /**
     * @return object
     */
    protected function scanPrepend()
    {
        if (preg_match('/^prepend +([^\n]+)/', $this->input, $matches)) {
            $this->consume($matches[0]);
            $token = $this->token('block', $matches[1]);
            $token->mode = 'prepend';

            return $token;
        }
    }

    /**
     * @return object
     */
    protected function scanAppend()
    {
        if (preg_match('/^append +([^\n]+)/', $this->input, $matches)) {
            $this->consume($matches[0]);
            $token = $this->token('block', $matches[1]);
            $token->mode = 'append';

            return $token;
        }
    }

    /**
     * @return object
     */
    protected function scanBlock()
    {
        if (preg_match("/^block\b *(?:(prepend|append) +)?([^\n]*)/", $this->input, $matches)) {
            $this->consume($matches[0]);
            $token = $this->token('block', $matches[2]);
            $token->mode = strlen($matches[1]) === 0 ? 'replace' : $matches[1];

            return $token;
        }
    }

    /**
     * @return object
     */
    protected function scanYield()
    {
        return $this->scan('/^yield */', 'yield');
    }

    /**
     * @return object
     */
    protected function scanInclude()
    {
        return $this->scan('/^include +([^\n]+)/', 'include');
    }
}
