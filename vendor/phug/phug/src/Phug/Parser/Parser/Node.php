<?php

namespace Phug\Parser;

use Phug\Ast\Node as AstNode;
use Phug\Lexer\TokenInterface;
use Phug\Util\Partial\LevelGetTrait;
use Phug\Util\SourceLocationInterface;

/**
 * Represents a node in the AST the parser generates.
 *
 * A node has children and always tries to reference its parents
 *
 * It also has some utility methods to work with those nodes
 */
class Node extends AstNode implements NodeInterface
{
    use LevelGetTrait;

    private $sourceLocation;
    private $outerNode;
    private $token;

    /**
     * Creates a new, detached node without children or a parent.
     *
     * It can be appended to any node after that
     *
     * @param SourceLocationInterface|null $sourceLocation
     * @param int|null                     $level          the level of indentation this node is at
     * @param NodeInterface                $parent         the parent of this node
     * @param NodeInterface[]              $children       the children of this node
     * @param TokenInterface               $token          the token that created the node
     */
    public function __construct(
        TokenInterface $token = null,
        SourceLocationInterface $sourceLocation = null,
        $level = null,
        NodeInterface $parent = null,
        array $children = null
    ) {
        parent::__construct($parent, $children);

        $this->token = $token;
        $this->sourceLocation = $sourceLocation ?: ($token ? $token->getSourceLocation() : null);
        $this->level = $level ?: 0;
        $this->outerNode = null;
    }

    /**
     * @return SourceLocationInterface|null
     */
    public function getSourceLocation()
    {
        return $this->sourceLocation;
    }

    /**
     * @return NodeInterface
     */
    public function getOuterNode()
    {
        return $this->outerNode;
    }

    /**
     * @return TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param NodeInterface $node
     *
     * @return $this
     */
    public function setOuterNode(NodeInterface $node = null)
    {
        $this->outerNode = $node;

        return $this;
    }
}
