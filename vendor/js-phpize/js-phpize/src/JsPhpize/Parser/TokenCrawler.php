<?php

namespace JsPhpize\Parser;

abstract class TokenCrawler
{
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
        return new Exception('Unexpected ' . $token->type . rtrim(' ' . ($token->value ?: '')) . $this->exceptionInfos(), 8);
    }
}
