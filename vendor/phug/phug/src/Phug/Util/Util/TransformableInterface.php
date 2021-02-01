<?php

namespace Phug\Util;

interface TransformableInterface
{
    /**
     * Prevent the expression from being transformed by customization patterns.
     */
    public function preventFromTransformation();

    /**
     * @return bool
     */
    public function isTransformationAllowed();
}
