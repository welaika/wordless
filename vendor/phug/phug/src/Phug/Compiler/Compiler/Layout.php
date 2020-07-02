<?php

namespace Phug\Compiler;

use Phug\CompilerInterface;
use Phug\Formatter\Element\DocumentElement;
use Phug\Formatter\ElementInterface;

class Layout
{
    /**
     * @var DocumentElement
     */
    private $document;

    /**
     * @var CompilerInterface
     */
    private $compiler;

    public function __construct(ElementInterface $document, CompilerInterface $compiler)
    {
        $this->document = $document;
        $this->compiler = $compiler;
    }

    public function getCompiler()
    {
        return $this->compiler;
    }

    public function getDocument()
    {
        return $this->document;
    }
}
