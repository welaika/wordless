<?php

namespace Phug\Compiler\Event;

use Phug\CompilerEvent;
use Phug\Event;

class CompileEvent extends Event
{
    private $input;
    private $path;

    /**
     * CompileEvent constructor.
     *
     * @param string      $input
     * @param string|null $path
     */
    public function __construct($input, $path = null)
    {
        parent::__construct(CompilerEvent::COMPILE);

        $this->input = $input;
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param string $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }

    /**
     * @return string|null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string|null $path
     */
    public function setPath($path = null)
    {
        $this->path = $path;
    }
}
