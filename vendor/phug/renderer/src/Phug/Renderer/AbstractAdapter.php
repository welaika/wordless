<?php

namespace Phug\Renderer;

use Phug\Renderer;
use Phug\Util\Partial\OptionTrait;
use Phug\Util\SandBox;

abstract class AbstractAdapter implements AdapterInterface
{
    use OptionTrait;

    private $renderer;

    public function __construct(Renderer $renderer, $options)
    {
        $this->renderer = $renderer;

        $this->setOptions($options);
    }

    public function getRenderer()
    {
        return $this->renderer;
    }

    public function captureBuffer(callable $display)
    {
        $throwable = null;
        $sandBox = new SandBox($display);

        if ($throwable = $sandBox->getThrowable()) {
            throw $throwable;
        }

        return $sandBox->getBuffer();
    }

    public function render($php, array $parameters)
    {
        return $this->captureBuffer(function () use ($php, $parameters) {
            $this->display($php, $parameters);
        });
    }
}
