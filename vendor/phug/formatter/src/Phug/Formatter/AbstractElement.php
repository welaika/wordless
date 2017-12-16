<?php

namespace Phug\Formatter;

use Phug\Ast\Node;
use Phug\Ast\NodeInterface;
use Phug\Parser\NodeInterface as ParserNode;

abstract class AbstractElement extends Node implements ElementInterface
{
    /**
     * @var ParserNode
     */
    private $originNode;

    /**
     * AbstractElement constructor.
     *
     * @param ParserNode|null    $originNode
     * @param NodeInterface|null $parent
     * @param array|null         $children
     */
    public function __construct(ParserNode $originNode = null, NodeInterface $parent = null, array $children = null)
    {
        $this->originNode = $originNode;

        parent::__construct($parent, $children);
    }

    public function dump()
    {
        $name = preg_replace('/^Phug\\\\.*\\\\([^\\\\]+)Element$/', '$1', get_class($this));
        if (method_exists($this, 'getName')) {
            $name .= ': '.$this->getName();
        }
        $lines = [$name];
        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $child) {
                $dump = method_exists($child, 'dump')
                    ? $child->dump()
                    : get_class($child);
                foreach (explode("\n", $dump) as $line) {
                    $lines[] = '  '.$line;
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @return ParserNode
     */
    public function getOriginNode()
    {
        return $this->originNode;
    }
}
