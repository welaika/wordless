<?php

namespace Phug\Renderer\Event;

use Phug\Event;
use Phug\RendererEvent;

class RenderEvent extends Event
{
    private $input;
    private $path;
    private $method;
    private $parameters;

    /**
     * CompileEvent constructor.
     *
     * @param string      $input
     * @param string|null $path
     * @param string      $method
     * @param array       $parameters
     */
    public function __construct($input, $path, $method, $parameters)
    {
        parent::__construct(RendererEvent::RENDER);

        $this->input = $input;
        $this->path = $path;
        $this->method = $method;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
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
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return null|string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param null|string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
}
