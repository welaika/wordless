<?php

namespace Jade\Lexer;

/**
 * Class Jade\Lexer\InputHandler.
 */
abstract class InputHandler
{
    /**
     * @var string
     */
    public $input;

    /**
     * @var array
     */
    protected $deferred = array();

    /**
     * @var array
     */
    protected $indentStack = array();

    /**
     * @var array
     */
    protected $stash = array();

    /**
     * Set lexer input.
     *
     * @param string $input input string
     */
    public function setInput($input)
    {
        $this->input = preg_replace("/\r\n|\r/", "\n", $input);
        $this->lineno = 1;
        $this->deferred = array();
        $this->indentStack = array();
        $this->stash = array();
    }

    /**
     * @return int
     */
    public function length()
    {
        return strlen($this->input);
    }

    /**
     * @param $code
     *
     * @return string
     */
    protected function normalizeCode($code)
    {
        return $code = (substr($code, -1) === ':' && substr($code, -2, 1) !== ':')
            ? substr($code, 0, -1)
            : $code;
    }

    protected function testIndent($indent)
    {
        if (!preg_match('/^' . $indent . '/', substr($this->input, 1), $matches)) {
            return;
        }

        if (!isset($this->identRE)) {
            $this->identRE = $indent;
        }

        return array(
            "\n" . $matches[0],
            $matches[0],
        );
    }

    protected function getNextIndent()
    {
        if (substr($this->input, 0, 1) !== "\n") {
            return;
        }

        $indents = isset($this->identRE)
            ? array($this->identRE)
            : ($this->allowMixedIndent
                ? array('[\\t ]*')
                : array('\\t+', ' *')
            );

        foreach ($indents as $indent) {
            if ($matches = $this->testIndent($indent)) {
                return $matches;
            }
        }
    }

    protected function getWhiteSpacesTokens($indents)
    {
        if ($indents && count($this->indentStack) && $indents === $this->indentStack[0]) {
            return $this->token('newline');
        }

        if ($indents) {
            array_unshift($this->indentStack, $indents);

            return $this->token('indent', $indents);
        }

        return $this->token('newline');
    }

    protected function getTokenFromIndent($firstChar, $indents)
    {
        if ($this->length() && $firstChar === "\n") {
            return $this->token('newline');
        }

        if (count($this->indentStack) && $indents < $this->indentStack[0]) {
            while (count($this->indentStack) && $indents < $this->indentStack[0]) {
                array_push($this->stash, $this->token('outdent'));
                array_shift($this->indentStack);
            }

            return array_pop($this->stash);
        }

        return $this->getWhiteSpacesTokens($indents);
    }
}
