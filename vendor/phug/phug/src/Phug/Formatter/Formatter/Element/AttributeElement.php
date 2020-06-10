<?php

namespace Phug\Formatter\Element;

use Phug\Ast\NodeInterface;
use Phug\Parser\NodeInterface as ParserNode;
use Phug\Util\Partial\NameTrait;
use Phug\Util\Partial\VariadicTrait;

class AttributeElement extends AbstractValueElement
{
    use NameTrait;
    use VariadicTrait;

    /**
     * AttributeElement constructor.
     *
     * @param string                   $name
     * @param string|ExpressionElement $value
     * @param ParserNode|null          $originNode
     * @param NodeInterface|null       $parent
     * @param array|null               $children
     */
    public function __construct(
        $name,
        $value,
        ParserNode $originNode = null,
        NodeInterface $parent = null,
        array $children = null
    ) {
        parent::__construct($originNode, $parent, $children);

        $this->setName($name);
        $this->setValue($value);
    }

    public function setValue($value)
    {
        parent::setValue($value);
        if ($value instanceof ExpressionElement) {
            $value->linkTo($this);
        }
    }
}
