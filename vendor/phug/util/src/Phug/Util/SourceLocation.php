<?php

namespace Phug\Util;

use Phug\Util\Partial\SourceLocationTrait;

class SourceLocation implements SourceLocationInterface
{
    use SourceLocationTrait;

    public function __construct($path, $line, $offset, $offsetLength = null)
    {
        $this->path = $path;
        $this->line = $line;
        $this->offset = $offset;
        $this->offsetLength = $offsetLength ?: 0;
    }
}
