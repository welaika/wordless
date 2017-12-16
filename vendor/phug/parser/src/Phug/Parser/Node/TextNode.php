<?php

namespace Phug\Parser\Node;

use Phug\Parser\Node;
use Phug\Util\Partial\EscapeTrait;
use Phug\Util\Partial\ValueTrait;

class TextNode extends Node
{
    use ValueTrait;
    use EscapeTrait;

    private $level = null;

    private $indent = '  ';

    public function setIndent($indent)
    {
        $this->indent = $indent;
    }

    public function getIndent()
    {
        return $this->indent;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function getLevel()
    {
        return $this->level;
    }
}
