<?php

namespace Phug\Renderer;

use Phug\Renderer;
use Phug\Util\OptionInterface;

interface AdapterInterface extends OptionInterface
{
    /**
     * @param array|\ArrayObject $options
     */
    public function __construct(Renderer $renderer, $options);

    /**
     * Return the renderer the adapter was constructed with.
     *
     * @return Renderer
     */
    public function getRenderer();

    /**
     * Capture buffered output of a callable display action.
     *
     * @param callable $display the action that potentially send output to the buffer.
     *
     * @throws \Throwable
     *
     * @return mixed
     */
    public function captureBuffer(callable $display);

    /**
     * Return renderer HTML.
     *
     * @param string $php        PHP srouce code
     * @param array  $parameters variables names and values
     *
     * @return string
     */
    public function render($php, array $parameters);

    /**
     * Display renderer HTML.
     *
     * @param string $php        PHP srouce code
     * @param array  $parameters variables names and values
     */
    public function display($php, array $parameters);
}
