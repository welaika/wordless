<?php

namespace JsPhpize\Lexer;

class StringLexer
{
    /**
     * String for the future token.
     *
     * @var string
     */
    protected $string = '';

    /**
     * Rest of the input to read.
     *
     * @var string|null
     */
    protected $input = null;

    /**
     * Current lexer.
     *
     * @var Lexer|null
     */
    protected $lexer = null;

    public function __construct(Lexer $lexer, string $input)
    {
        $this->lexer = $lexer;
        $this->input = $input;
    }

    public function verse(string $chunk)
    {
        $this->string .= $chunk;
        $this->input = mb_substr($this->input, mb_strlen($chunk));
    }

    public function getBackTickString(): string
    {
        while (preg_match('/^(\\\\.|\\$(?!{)|[^`$])*\\${/', $this->input, $interpolation)) {
            $this->verse($interpolation[0]);

            if (preg_match('#^[^{}`"\'/]*}#', $this->input, $match)) {
                $this->verse($match[0]);

                continue;
            }

            $this->verse(mb_substr($this->input, 0, $this->lexer->getNextParseLength($this->input)));
        }

        if (!preg_match('/^(\\\\.|\\$(?!{)|[^`$])*`/U', $this->input, $match)) {
            throw new Exception('Unterminated ` string after ' . $this->string, 27);
        }

        return $this->string . $match[0];
    }
}
