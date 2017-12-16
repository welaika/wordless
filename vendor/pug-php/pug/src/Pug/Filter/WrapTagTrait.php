<?php

namespace Pug\Filter;

trait WrapTagTrait
{
    public function wrapInTag($code)
    {
        if (isset($this->tag)) {
            $code = '<' . $this->tag . (isset($this->textType) ? ' type="text/' . $this->textType . '"' : '') . '>' .
                $code .
                '</' . $this->tag . '>';
        }

        return $code;
    }
}
