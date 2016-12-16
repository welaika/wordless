<?php

namespace JsPhpize\Nodes;

class Comment
{
    protected $content;

    public function __construct($content)
    {
        $this->content = trim($content);
    }

    public function isMultiline()
    {
        return substr($this->content, 0, 2) === '/*';
    }

    public function __toString()
    {
        return $this->content;
    }
}
