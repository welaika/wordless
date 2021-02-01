<?php

namespace Phug\Partial;

use Phug\Phug;
use Phug\PhugException;
use Phug\Renderer;

trait PluginEnablerTrait
{
    /**
     * Enable a plugin as both an extension and a module either globally or on a given renderer.
     *
     * @param Renderer|null $renderer
     *
     * @throws PhugException
     */
    public static function enable(Renderer $renderer = null)
    {
        if ($renderer) {
            static::activateOnRenderer($renderer);

            return;
        }

        Phug::addExtension(static::class);

        if (Phug::isRendererInitialized()) {
            static::activateOnRenderer(Phug::getRenderer());
        }
    }

    /**
     * Globally disable a plugin as both an extension and a module.
     *
     * @param Renderer|null $renderer
     *
     * @throws PhugException
     */
    public static function disable()
    {
        Phug::removeExtension(static::class);

        if (Phug::isRendererInitialized()) {
            $renderer = Phug::getRenderer();

            if ($renderer->hasModule(static::class)) {
                $renderer->removeModule(static::class);
            }
        }
    }

    private static function activateOnRenderer(Renderer $renderer)
    {
        if (!$renderer->hasModule(static::class)) {
            $renderer->addModule(static::class);
        }
    }
}
