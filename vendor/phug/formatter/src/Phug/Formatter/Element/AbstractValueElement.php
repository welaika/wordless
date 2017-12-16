<?php

namespace Phug\Formatter\Element;

use Phug\Ast\NodeInterface;
use Phug\Formatter\AbstractElement;
use Phug\Parser\NodeInterface as ParserNode;
use Phug\Util\Partial\ValueTrait;

abstract class AbstractValueElement extends AbstractElement
{
    use ValueTrait;

    /**
     * AbstractValueElement constructor.
     *
     * @param string|ExpressionElement $value
     * @param ParserNode|null          $originNode
     * @param NodeInterface|null       $parent
     * @param array|null               $children
     */
    public function __construct(
        $value = null,
        ParserNode $originNode = null,
        NodeInterface $parent = null,
        array $children = null
    ) {
        parent::__construct($originNode, $parent, $children);

        if ($value !== null) {
            $this->setValue($value);
        }
    }
}
