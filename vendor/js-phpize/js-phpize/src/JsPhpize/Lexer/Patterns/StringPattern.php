<?php

namespace JsPhpize\Lexer\Patterns;

use Generator;
use JsPhpize\Lexer\Lexer;
use JsPhpize\Lexer\Pattern;
use JsPhpize\Lexer\StringLexer;

class StringPattern extends Pattern
{
    public function __construct($priority)
    {
        parent::__construct($priority, 'string', implode('|', array_map(function ($delimiter) {
            return "$delimiter(?:\\\\.|[^$delimiter\\\\])*$delimiter";
        }, ['"', "'", '`'])));
    }

    public function lexWith(Lexer $lexer): Generator
    {
        $input = $lexer->rest();

        if (!preg_match('/^\s*`/', $input, $match)) {
            foreach (parent::lexWith($lexer) as $token) {
                yield $token;
            }

            return;
        }

        $stringLexer = new StringLexer($lexer, $input);
        $stringLexer->verse($match[0]);

        yield $lexer->consumeStringToken($stringLexer->getBackTickString());
    }
}
