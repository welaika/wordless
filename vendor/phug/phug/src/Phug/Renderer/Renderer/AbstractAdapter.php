<?php

namespace Phug\Renderer;

use ArrayObject;
use Closure;
use Phug\Renderer;
use Phug\Util\Partial\OptionTrait;
use Throwable;

/**
 * Extending AbstractAdapter gives you helpers to create an adapter in an easier way.
 *
 * It mainly brings:
 * - options methods (see OptionTrait)
 * - a link to the current renderer (->getRenderer())
 * - a default render method based on capturing output of the display method
 */
abstract class AbstractAdapter implements AdapterInterface
{
    use OptionTrait;

    private $renderer;

    /**
     * AbstractAdapter constructor.
     *
     * @param Renderer          $renderer current renderer used.
     * @param array|ArrayObject $options  options array/object to be propagated from renderer to the adapter.
     */
    public function __construct(Renderer $renderer, $options)
    {
        $this->renderer = $renderer;

        $this->setOptions($options);
    }

    /**
     * Get the current linked renderer.
     *
     * @return Renderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Get a callable that display/output text/HTML and return as string what those
     * callable would have displayed.
     *
     * @param callable $display function/closure that display renderer code.
     *
     * @throws Throwable
     *
     * @return string
     */
    public function captureBuffer(callable $display)
    {
        $throwable = null;
        $sandBox = $this->getRenderer()->getNewSandBox($display);

        if ($throwable = $sandBox->getThrowable()) {
            throw $throwable;
        }

        return $sandBox->getBuffer();
    }

    /**
     * Return rendered code based on capturing output of the ->display() method.
     *
     * @param string $php        PHP code resulting from the rendering.
     * @param array  $parameters Render local variables.
     *
     * @throws Throwable
     *
     * @return string
     */
    public function render($php, array $parameters)
    {
        return $this->captureBuffer(function () use ($php, $parameters) {
            $this->display($php, $parameters);
        });
    }

    /**
     * Bind context ($this) to a given closure if passed in local variables, and bind the current adapter
     * as __pug_adapter variable.
     *
     * @param Closure $execution Function to be executed to render/display the rendered template.
     * @param array   $variables Render local variables.
     */
    protected function execute(Closure $execution, array &$variables)
    {
        if (isset($variables['this'])) {
            $execution = $execution->bindTo($variables['this']);
            unset($variables['this']);
        }

        $variables['__pug_adapter'] = $this;

        $execution();
    }
}
