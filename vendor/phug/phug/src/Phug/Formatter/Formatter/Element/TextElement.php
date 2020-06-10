<?php

namespace Phug\Formatter\Element;

use Phug\Util\Partial\EscapeTrait;

class TextElement extends AbstractValueElement
{
    use EscapeTrait;

    /**
     * @var bool
     */
    protected $end;

    /**
     * @param mixed $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @return bool
     */
    public function isEnd()
    {
        return $this->end;
    }
}
