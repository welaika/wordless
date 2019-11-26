<?php

namespace Phug\Lexer\Event;

use Phug\Event;
use Phug\LexerEvent;

class LexEvent extends Event
{
    private $input;
    private $path;
    private $stateClassName;
    private $stateOptions;

    /**
     * LexEvent constructor.
     *
     * @param string      $input
     * @param string|null $path
     * @param string      $stateClassName
     * @param array       $stateOptions
     */
    public function __construct($input, $path, $stateClassName, array $stateOptions)
    {
        parent::__construct(LexerEvent::LEX);

        $this->input = $input;
        $this->path = $path;
        $this->stateClassName = $stateClassName;
        $this->stateOptions = $stateOptions;
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

    /**
     * @return string
     */
    public function getStateClassName()
    {
        return $this->stateClassName;
    }

    /**
     * @param string $stateClassName
     */
    public function setStateClassName($stateClassName)
    {
        $this->stateClassName = $stateClassName;
    }

    /**
     * @return array
     */
    public function getStateOptions()
    {
        return $this->stateOptions;
    }

    /**
     * @param array $stateOptions
     */
    public function setStateOptions($stateOptions)
    {
        $this->stateOptions = $stateOptions;
    }
}
