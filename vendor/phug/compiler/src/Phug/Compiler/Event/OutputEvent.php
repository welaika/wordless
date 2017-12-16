<?php

namespace Phug\Compiler\Event;

use Phug\CompilerEvent;
use Phug\Event;

class OutputEvent extends Event
{
    private $compileEvent;
    private $output;

    /**
     * OutputEvent constructor.
     *
     * @param string $output
     */
    public function __construct(CompileEvent $compileEvent, $output)
    {
        parent::__construct(CompilerEvent::OUTPUT);

        $this->compileEvent = $compileEvent;
        $this->output = $output;
    }

    /**
     * @return CompileEvent
     */
    public function getCompileEvent()
    {
        return $this->compileEvent;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param string $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }
}
