<?php

namespace Phug\Formatter;

interface MarkupInterface extends AssignmentContainerInterface
{
    public function belongsTo(array $tagList);

    public function isAutoClosed();
}
