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

    public function getRenderer();

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
