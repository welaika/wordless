<?php

namespace Phug\Formatter\Element;

use Phug\Ast\NodeInterface;
use Phug\Parser\NodeInterface as ParserNode;
use Phug\Util\Partial\AttributeTrait;
use Phug\Util\Partial\NameTrait;

class MarkupElement extends AbstractMarkupElement
{
    use AttributeTrait;
    use NameTrait;

    /**
     * @var bool
     */
    protected $autoClosed = false;

    /**
     * MarkupElement constructor.
     *
     * @param string                 $name
     * @param bool                   $autoClosed
     * @param \SplObjectStorage|null $attributes
     * @param ParserNode|null        $originNode
     * @param NodeInterface|null     $parent
     * @param array|null             $children
     */
    public function __construct(
        $name,
        $autoClosed = false,
        \SplObjectStorage $attributes = null,
        ParserNode $originNode = null,
        NodeInterface $parent = null,
        array $children = null
    ) {
        parent::__construct($originNode, $parent, $children);

        $this->setName($name);
        $this->autoClosed = $autoClosed;

        if ($attributes) {
            $this->getAttributes()->addAll($attributes);
        }
    }

    public function getAttribute($name)
    {
        foreach ($this->getAttributes() as $attribute) {
            if ($attribute->getName() === $name) {
                return $attribute->getValue();
            }
        }
    }

    /**
     * @return bool
     */
    public function isAutoClosed()
    {
        return $this->autoClosed;
    }
}
