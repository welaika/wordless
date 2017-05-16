<?php

namespace Jade\Compiler;

use Jade\Nodes\Tag;
use Jade\Nodes\Text;

abstract class TagVisitor extends Visitor
{
    /**
     * @param Nodes\Tag $tag
     */
    protected function visitTagAttributes(Tag $tag, $newLinePrettyPrint, $close = '>')
    {
        $open = '<' . $tag->name;

        if (count($tag->attributes)) {
            $this->buffer($this->indent() . $open, false);
            $this->visitAttributes($tag->attributes);
            $this->buffer($this->getClassesDisplayCode() . $close . $this->newline(), false);

            return;
        }

        $this->buffer($open . $close, $newLinePrettyPrint ? null : false);
    }

    /**
     * @param Nodes\Tag $tag
     */
    protected function initTagName(Tag $tag)
    {
        if (isset($tag->buffer)) {
            if (preg_match('`^[a-z][a-zA-Z0-9]+(?!\()`', $tag->name)) {
                $tag->name = '$' . $tag->name;
            }
            $tag->name = trim($this->createCode('echo ' . $tag->name . ';'));
        }
    }

    protected function trimLastLine()
    {
        $key = count($this->buffer) - 1;
        $this->buffer[$key] = substr($this->buffer[$key], 0, -1);
        if ($this->prettyprint && substr($this->buffer[$key], -1) === ' ') {
            $this->buffer[$key] = substr($this->buffer[$key], 0, -1);
        }
    }

    protected function insertSpacesBetweenBlockNodes($nodes)
    {
        $count = count($nodes);
        for ($i = 1; $i < $count; $i++) {
            if (
                $nodes[$i] instanceof Text &&
                $nodes[$i - 1] instanceof Text &&
                !preg_match('/^\s/', $nodes[$i]->value) &&
                !preg_match('/\s$/', $nodes[$i - 1]->value)
            ) {
                $nodes[$i - 1]->value .= ' ';
            }
        }
    }

    /**
     * @param Nodes\Tag $tag
     */
    protected function visitTagContents(Tag $tag)
    {
        $inc = $tag->keepWhiteSpaces() ? -$this->indents : 1;
        $this->indents += $inc;
        if (isset($tag->code)) {
            $this->visitCode($tag->code);
        }
        if (!$tag->keepWhiteSpaces()) {
            $this->insertSpacesBetweenBlockNodes($tag->block->nodes);
        }
        $this->visit($tag->block);
        if ($tag->keepWhiteSpaces() && substr(end($this->buffer), -1) === "\n") {
            $this->trimLastLine();
        }
        $this->indents -= $inc;
    }

    /**
     * @param Nodes\Tag $tag
     */
    protected function compileTag(Tag $tag)
    {
        $selfClosing = (in_array(strtolower($tag->name), $this->selfClosing) || $tag->selfClosing) && !$this->xml;
        $this->visitTagAttributes($tag, !$tag->keepWhiteSpaces() && $this->prettyprint, (!$selfClosing || $this->terse) ? '>' : ' />');

        if (!$selfClosing) {
            $this->visitTagContents($tag);
            $this->buffer('</' . $tag->name . '>', $tag->keepWhiteSpaces() ? false : null);
        }
    }

    /**
     * @param Nodes\Tag $tag
     */
    protected function visitTag(Tag $tag)
    {
        $this->initTagName($tag);

        $insidePrettyprint = !$tag->canInline() && $this->prettyprint && !$tag->isInline();
        $prettyprint = $tag->keepWhiteSpaces() || $insidePrettyprint;

        if ($this->prettyprint && !$insidePrettyprint) {
            $this->buffer[] = $this->indent();
        }

        $this->tempPrettyPrint($prettyprint, 'compileTag', $tag);

        if (!$prettyprint && $this->prettyprint && !$tag->isInline()) {
            $this->buffer[] = $this->newline();
        }
    }
}
