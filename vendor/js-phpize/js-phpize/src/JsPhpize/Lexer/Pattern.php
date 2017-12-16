<?php

namespace JsPhpize\Lexer;

use JsPhpize\Readable;

class Pattern extends Readable
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $priority;

    /**
     * @var string
     */
    protected $regex;

    public function __construct($priority, $type, $patterns, $notInsideAWord = false)
    {
        $this->priority = $priority;
        $this->type = $type;
        $this->regex = is_array($patterns)
            ? implode('|', array_map(function ($pattern) {
                return preg_quote($pattern, '/');
            }, $patterns))
            : $patterns;
        $exception = false;
        if ($notInsideAWord !== false) {
            $exception = 'a-zA-Z0-9\\\\_\\x7f-\\xff';
            if (is_string($notInsideAWord)) {
                $exception .= preg_quote($notInsideAWord, '/');
            }
        }
        $this->regex = '(' . $this->regex . ')';
        if ($exception) {
            $this->regex = '(?<![' . $exception . '])' . $this->regex . '(?![' . $exception . '])';
        }
    }
}
