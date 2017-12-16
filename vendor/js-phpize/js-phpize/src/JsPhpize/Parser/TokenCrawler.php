<?php

namespace JsPhpize\Parser;

use JsPhpize\Lexer\Lexer;

abstract class TokenCrawler
{
    /**
     * @var Lexer
     */
    protected $lexer;

    /**
     * @var array
     */
    protected $tokens;

    protected function retrieveNext()
    {
        while (($next = $this->lexer->next()) && $next->isNeutral());

        return $next;
    }

    protected function next()
    {
        return array_shift($this->tokens) ?: $this->retrieveNext();
    }

    protected function skip()
    {
        $this->next();
    }

    protected function get($index)
    {
        while ($index >= count($this->tokens)) {
            $this->tokens[] = $this->retrieveNext();
        }

        return $this->tokens[$index];
    }

    protected function exceptionInfos()
    {
        return $this->lexer->exceptionInfos();
    }

    protected function unexpected($token)
    {
        return $this->lexer->unexpected($token, '\\JsPhpize\\Parser\\Exception');
    }
}
