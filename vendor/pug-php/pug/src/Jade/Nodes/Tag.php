<?php

namespace Jade\Nodes;

class Tag extends Attributes
{
    protected static $inlineTags = array(
        'a',
        'abbr',
        'acronym',
        'b',
        'br',
        'code',
        'em',
        'font',
        'i',
        'img',
        'ins',
        'kbd',
        'map',
        'samp',
        'small',
        'span',
        'strong',
        'sub',
        'sup',
    );
    protected static $whiteSpacesTags = array(
        'pre',
        'script',
        'textarea',
    );
    public $name;
    public $attributes;
    public $block;
    public $selfClosing = false;

    public function __construct($name, $block = null)
    {
        $this->name = $name;

        $this->block = $block !== null
            ? $block
            : new Block();

        $this->attributes = array();
    }

    public function isInline()
    {
        return in_array($this->name, static::$inlineTags);
    }

    public function keepWhiteSpaces()
    {
        return in_array($this->name, static::$whiteSpacesTags);
    }

    public function hasConsecutiveTextNodes()
    {
        $nodes = $this->block->nodes;
        $prev = null;

        foreach ($nodes as $key => $node) {
            if ($prev !== null && isset($nodes[$prev]->isText) && $nodes[$prev]->isText && isset($node->isText) && $node->isText) {
                return false;
            }
            $prev = $key;
        }

        return true;
    }

    public function hasOnlyInlineNodes()
    {
        foreach ($this->block->nodes as $node) {
            if (!$node->isInline()) {
                return false;
            }
        }

        return true;
    }

    public function canInline()
    {
        return $this->hasOnlyInlineNodes() && $this->hasConsecutiveTextNodes();
    }
}
