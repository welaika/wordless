<?php

namespace Phug\Formatter\Element;

use Phug\Ast\NodeInterface;
use Phug\Parser\NodeInterface as ParserNode;

class AnonymousBlockElement extends ExpressionElement
{
    public function __construct(ParserNode $originNode = null, NodeInterface $parent = null, array $children = null)
    {
        parent::__construct('$__pug_children(get_defined_vars())', $originNode, $parent, $children);
        $this->uncheck();
        $this->preventFromTransformation();
    }
}
