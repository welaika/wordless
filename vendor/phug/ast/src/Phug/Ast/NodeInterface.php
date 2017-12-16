<?php

namespace Phug\Ast;

use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 * Represents a node in a tree-like structure.
 *
 * All methods should automatically keep a consistent state inside the tree,
 * e.g. if `$someNode->setParent(null)` is done, the previous parent shouldn't have
 * `$someNode` in its children anymore.
 */
interface NodeInterface extends IteratorAggregate, Countable, ArrayAccess
{
    /**
     * Creates a recursive, detached copy of the current node.
     */
    public function __clone();

    /**
     * Checks if the current node has a parent-node.
     *
     * @return NodeInterface
     */
    public function hasParent();

    /**
     * Gets the currently associated parent-node.
     *
     * If the node has no parent, `null` is returned.
     *
     * @return NodeInterface|null
     */
    public function getParent();

    /**
     * Sets the nodes parent to a new one.
     *
     * This will automatically remove it from the old parent, if any, and append it to the new one.
     *
     * @param NodeInterface $parent
     *
     * @return $this
     */
    public function setParent(NodeInterface $parent);

    /**
     * Returns `true` if this node has any children, `false` if not.
     *
     * @return bool
     */
    public function hasChildren();

    /**
     * Returns the amount of children attached to this node.
     *
     * @return int
     */
    public function getChildCount();

    /**
     * Returns the numerical index of a child-node.
     *
     * @param NodeInterface $child
     *
     * @return int|false
     */
    public function getChildIndex(NodeInterface $child);

    /**
     * Returns an array of child-nodes attached to this node.
     *
     * @return NodeInterface[]
     */
    public function getChildren();

    /**
     * Replaces all children with new ones.
     *
     * This will remove all nodes from the current node.
     *
     * @param NodeInterface[] $children
     *
     * @return mixed
     */
    public function setChildren(array $children);

    /**
     * Removes all children from this node.
     *
     * All parents of the child-nodes are set to `null` respectively.
     *
     * @return $this
     */
    public function removeChildren();

    /**
     * Checks if this node has a certain child among its children.
     *
     * @param NodeInterface $child
     *
     * @return bool
     */
    public function hasChild(NodeInterface $child);

    /**
     * Checks if this node has a child at a specified numerical index.
     *
     * @param $index
     *
     * @return bool
     */
    public function hasChildAt($index);

    /**
     * Gets a child at a specified numerical index.
     *
     * @param $index
     *
     * @return NodeInterface
     */
    public function getChildAt($index);

    /**
     * Removes a child at a specified numerical index.
     *
     * @param $index
     *
     * @return $this
     */
    public function removeChildAt($index);

    /**
     * Appends a child to the child-list.
     *
     * @param NodeInterface $child
     *
     * @return $this
     */
    public function appendChild(NodeInterface $child);

    /**
     * Prepends a child to the child-list.
     *
     * @param NodeInterface $child
     *
     * @return $this
     */
    public function prependChild(NodeInterface $child);

    /**
     * Removes a child from the child-list.
     *
     * This is detaching. The child and its own child-nodes are still intact, but it's detached
     * from the tree completely and acts as an own root-node.
     *
     * @param NodeInterface $child
     *
     * @return $this
     */
    public function removeChild(NodeInterface $child);

    /**
     * Inserts a child before another child in the child-list.
     *
     * @param NodeInterface $child
     * @param NodeInterface $newChild
     *
     * @return $this
     */
    public function insertBefore(NodeInterface $child, NodeInterface $newChild);

    /**
     * Inserts a child after another child in the child-list.
     *
     * @param NodeInterface $child
     * @param NodeInterface $newChild
     *
     * @return $this
     */
    public function insertAfter(NodeInterface $child, NodeInterface $newChild);

    /**
     * Returns the numerical index of this child inside its parent.
     *
     * Returns the index
     *
     * @return int|false
     */
    public function getIndex();

    /**
     * Gets the previous sibling of this child inside its parent.
     *
     * @return NodeInterface
     */
    public function getPreviousSibling();

    /**
     * Gets the next sibling of this child inside its parent.
     *
     * @return NodeInterface
     */
    public function getNextSibling();

    /**
     * Appends a child right after this node in its parent.
     *
     * @param NodeInterface $child
     *
     * @return $this
     */
    public function append(NodeInterface $child);

    /**
     * Prepends a child before this node in its parent.
     *
     * @param NodeInterface $child
     *
     * @return $this
     */
    public function prepend(NodeInterface $child);

    /**
     * Removes/detaches this node from the tree.
     *
     * @return $this
     */
    public function remove();

    /**
     * Validates a child by a given callback.
     *
     * The callback receives this node as the first parameter.
     *
     * @param callable $callback
     *
     * @return bool
     */
    public function is(callable $callback);

    /**
     * Traverses the tree and returns all child elements that match the given callback.
     *
     * This uses `$this->is($callback)` internally.
     *
     * This method is exclusive, it doesn't include this child in its checks.
     *
     * @param callable $callback
     * @param int      $depth
     * @param int      $level
     *
     * @return \Generator
     */
    public function findChildren(callable $callback, $depth = null, $level = null);

    /**
     * Same as `findChildren()`, but returns an iterated array instead of a generator.
     *
     * @param callable $callback
     * @param int      $depth
     * @param int      $level
     *
     * @return NodeInterface[]
     */
    public function findChildrenArray(callable $callback, $depth = null, $level = null);

    /**
     * Traverses the tree and returns all child elements that match the given callback.
     *
     * This uses `$this->is($callback)` internally.
     *
     * This method is inclusive, it includes this child in its checks.
     *
     * @param callable $callback
     * @param int      $depth
     *
     * @return \Generator
     */
    public function find(callable $callback, $depth = null);

    /**
     * Same as `find()`, but returns an iterated array instead of a generator.
     *
     * @param callable $callback
     * @param int      $depth
     *
     * @return NodeInterface[]
     */
    public function findArray(callable $callback, $depth = null);
}
