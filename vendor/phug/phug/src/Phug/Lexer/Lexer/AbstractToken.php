<?php

namespace Phug\Lexer;

use Phug\Util\Partial\LevelGetTrait;
use Phug\Util\SourceLocationInterface;

abstract class AbstractToken implements TokenInterface, HandleTokenInterface
{
    use LevelGetTrait;

    private $sourceLocation;
    private $indentation;
    private $handled = false;

    public function __construct(SourceLocationInterface $sourceLocation = null, $level = null, $indentation = null)
    {
        $this->sourceLocation = $sourceLocation;
        $this->level = $level ?: 0;
        $this->indentation = $indentation;
    }

    /**
     * @return SourceLocationInterface
     */
    public function getSourceLocation()
    {
        return $this->sourceLocation;
    }

    public function getIndentation()
    {
        return $this->indentation;
    }

    public function isHandled()
    {
        return $this->handled;
    }

    public function markAsHandled()
    {
        $this->handled = true;
    }

    public function __toString()
    {
        return '['.get_class($this).']';
    }
}
