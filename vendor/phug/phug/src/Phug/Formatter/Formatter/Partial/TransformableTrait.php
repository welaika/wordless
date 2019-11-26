<?php

namespace Phug\Formatter\Partial;

trait TransformableTrait
{
    /**
     * @var bool
     */
    protected $transformable = true;

    /**
     * Prevent the expression from being transformed by customization patterns.
     */
    public function preventFromTransformation()
    {
        $this->transformable = false;
    }

    /**
     * @return bool
     */
    public function isTransformationAllowed()
    {
        return $this->transformable;
    }
}
