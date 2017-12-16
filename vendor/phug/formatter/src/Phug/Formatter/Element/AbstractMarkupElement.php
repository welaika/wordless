<?php

namespace Phug\Formatter\Element;

use Phug\Formatter\MarkupInterface;
use Phug\Formatter\Partial\MagicAccessorTrait;

abstract class AbstractMarkupElement extends AbstractAssignmentContainerElement implements MarkupInterface
{
    use MagicAccessorTrait;

    /**
     * Return true if the tag name is in the given list.
     *
     * @param array $tagList
     *
     * @return bool
     */
    public function belongsTo(array $tagList)
    {
        if (is_string($this->getName())) {
            return in_array(strtolower($this->getName()), $tagList);
        }

        return false;
    }
}
