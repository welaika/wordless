<?php

namespace Phug\Parser;

use Phug\Ast\NodeInterface as AstNodeInterface;
use Phug\Util\SourceLocationInterface;

interface NodeInterface extends AstNodeInterface
{
    /**
     * @return SourceLocationInterface|null
     */
    public function getSourceLocation();

    public function getToken();

    public function getLevel();

    public function getOuterNode();

    public function setOuterNode(self $node);
}
