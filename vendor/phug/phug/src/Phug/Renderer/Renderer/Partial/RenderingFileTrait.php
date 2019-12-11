<?php

namespace Phug\Renderer\Partial;

trait RenderingFileTrait
{
    /**
     * File currently rendering.
     *
     * @var string
     */
    private $renderingFile;

    /**
     * Get file currently rendering.
     *
     * @return string
     */
    public function getRenderingFile()
    {
        return $this->renderingFile;
    }
}
