<?php

namespace Phug\Formatter\Element;

use Phug\Ast\NodeInterface;
use Phug\Formatter\AbstractElement;
use Phug\Formatter\Partial\MagicAccessorTrait;
use Phug\Parser\NodeInterface as ParserNode;
use Phug\Util\Partial\NameTrait;
use Phug\Util\Partial\ValueTrait;

class KeywordElement extends AbstractElement
{
    use NameTrait;
    use ValueTrait;
    use MagicAccessorTrait;

    /**
     * KeywordElement constructor.
     *
     * @param string             $name
     * @param string             $value
     * @param ParserNode|null    $originNode
     * @param NodeInterface|null $parent
     * @param array|null         $children
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
}
