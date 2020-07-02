<?php

namespace Phug\Formatter;

use Phug\Ast\NodeInterface;

interface ElementInterface extends NodeInterface
{
    /**
     * @return string
     */
    public function dump();

    /**
     * @return \Phug\Parser\NodeInterface
     */
    public function getOriginNode();
}
