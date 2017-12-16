<?php

namespace Phug\Lexer;

use Phug\Util\SourceLocationInterface;

interface TokenInterface
{
    public function __construct(SourceLocationInterface $sourceLocation = null, $level = null, $indentation = null);

    /**
     * @return SourceLocationInterface|null
     */
    public function getSourceLocation();

    public function getLevel();

    public function getIndentation();
}
