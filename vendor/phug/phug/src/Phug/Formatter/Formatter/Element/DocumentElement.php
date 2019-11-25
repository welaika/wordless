<?php

namespace Phug\Formatter\Element;

class DocumentElement extends AbstractMarkupElement
{
    public function getName()
    {
        return 'document';
    }

    public function isAutoClosed()
    {
        return false;
    }
}
