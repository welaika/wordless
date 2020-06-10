<?php

namespace Phug\Ast;

use InvalidArgumentException;
use Phug\AstException;

/**
 * Represents a node in a tree-like data structure.
 */
class Node implements NodeInterface
{
    /**
     * Stores the current parent node of this node.
     *
     * @var NodeInterface
     */
    private $parent;

    /**
     * Stores an array of children that are attached to this node.
     *
     * @var NodeInterface[]
     */
    private $children;

    /**
     * Creates a new node instance.
     *
     * @param NodeInterface $parent
     * @param array         $children
     */
    public function __construct(NodeInterface $parent = null, array $children = null)
    {
        $this->parent = null;
        $this->children = [];

        if ($parent !== null) {
            $this->setParent($parent);
        }

        if ($children !== null) {
            $this->setChildren($children);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->parent = null;
        $children = $this->children;
        $this->children = [];
        foreach ($children as $child) {
            $this->appendChild(clone $child);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasParent()
    {
        return $this->parent !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(NodeInterface $parent = null)
    {
        if ($this->parent === $parent) {
            return $this;
        }

        if ($this->parent && $this->parent->hasChild($this)) {
            $this->parent->removeChild($this);
        }

        $this->parent = $parent;

        if ($parent !== null && !$parent->hasChild($this)) {
            $parent->appendChild($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        return !empty($this->children);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildCount()
    {
        return count($this->children);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildIndex(NodeInterface $child)
    {
        return array_search($child, $this->children, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function setChildren(array $children)
    {
        $this->removeChildren();
        foreach ($children as $child) {
            $this->appendChild($child);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChildren()
    {
        foreach ($this->children as $child) {
            $child->setParent(null);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChild(NodeInterface $child)
    {
        return in_array($child, $this->children, true);
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildAt($index)
    {
        return isset($this->children[$index]);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildAt($index)
    {
        if (!$this->hasChildAt($index)) {
            throw new AstException(
                "Failed to get child: No child found at $index"
            );
        }

        return $this->children[$index];
    }

    /**
     * {@inheritdoc}
     */
    public function removeChildAt($index)
    {
        return $this->removeChild($this->getChildAt($index));
    }

    /**
     * {@inheritdoc}
     */
    private function prepareChild(NodeInterface $child)
    {
        if ($this->hasChild($child)) {
            $this->removeChild($child);
        }
    }

    /**
     * {@inheritdoc}
     */
    private function finishChild(NodeInterface $child)
    {
        if ($child->getParent() !== $this) {
            $child->setParent($this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function appendChild(NodeInterface $child)
    {
        $this->prepareChild($child);
        $this->children[] = $child;
        $this->finishChild($child);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prependChild(NodeInterface $child)
    {
        $this->prepareChild($child);
        array_unshift($this->children, $child);
        $this->finishChild($child);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild(NodeInterface $child)
    {
        $idx = array_search($child, $this->children, true);

        if ($idx !== false) {
            array_splice($this->children, $idx, 1);
            $child->setParent(null);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function insertBefore(NodeInterface $child, NodeInterface $newChild)
    {
        if (!$this->hasChild($child)) {
            throw new AstException(
                'Failed to insert before: Passed child is not a child of element to insert in'
            );
        }

        $this->prepareChild($newChild);
        array_splice($this->children, $child->getIndex(), 0, [$newChild]);
        $this->finishChild($newChild);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function insertAfter(NodeInterface $child, NodeInterface $newChild)
    {
        if (!$this->hasChild($child)) {
            throw new AstException(
                'Failed to insert after: Passed child is not a child of element to insert in'
            );
        }

        $this->prepareChild($newChild);
        array_splice($this->children, $child->getIndex() + 1, 0, [$newChild]);
        $this->finishChild($newChild);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex()
    {
        if ($this->parent === null) {
            return;
        }

        return $this->parent->getChildIndex($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviousSibling()
    {
        $idx = $this->getIndex();
        if ($idx === null || $idx === 0) { //Includes "not found" and "0", which means this is the first sibling
            return;
        }

        return $this->parent->getChildAt($idx - 1);
    }

    /**
     * {@inheritdoc}
     */
    public function getNextSibling()
    {
        $idx = $this->getIndex();
        if ($idx === null || $idx >= count($this->parent) - 1) {
            return;
        }

        return $this->parent->getChildAt($idx + 1);
    }

    /**
     * {@inheritdoc}
     */
    public function append(NodeInterface $child)
    {
        $this->parent->insertAfter($this, $child);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(NodeInterface $child)
    {
        $this->parent->insertBefore($this, $child);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function remove()
    {
        $this->parent->removeChild($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function is(callable $callback)
    {
        return $callback($this) === true;
    }

    /**
     * {@inheritdoc}
     */
    public function findChildren(callable $callback, $depth = null, $level = null)
    {
        $level = $level ?: 0;

        foreach ($this->children as $child) {

            /** @var NodeInterface $child */
            if ($child->is($callback)) {
                yield $child;
            }

            if ($depth === null || $level < $depth) {
                if ($child instanceof NodeInterface) {
                    foreach ($child->findChildren($callback, $depth, $level + 1) as $subChild) {
                        yield $subChild;
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findChildrenArray(callable $callback, $depth = null, $level = null)
    {
        return iterator_to_array($this->findChildren($callback, $depth, $level));
    }

    /**
     * {@inheritdoc}
     */
    public function find(callable $callback, $depth = null)
    {
        if ($this->is($callback)) {
            yield $this;
        }

        foreach ($this->findChildren($callback, $depth) as $child) {
            yield $child;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findArray(callable $callback, $depth = null)
    {
        return iterator_to_array($this->find($callback, $depth));
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        foreach ($this->children as $child) {
            yield $child;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->getChildCount();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->hasChildAt($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->getChildAt($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (!($value instanceof NodeInterface)) {
            throw new InvalidArgumentException(
                'Argument 2 passed to Node->offsetSet needs to be instance '.
                'of '.NodeInterface::class
            );
        }

        if ($offset >= count($this)) {
            $this->appendChild($value);

            return;
        }

        $old = $this->getChildAt($offset);
        $old->append($value);
        $old->remove();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->removeChildAt($offset);
    }
}
