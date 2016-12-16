<?php

namespace Jade\Compiler;

use Jade\Nodes\Block;
use Jade\Nodes\Comment;
use Jade\Nodes\Doctype;
use Jade\Nodes\Literal;
use Jade\Nodes\Node;

abstract class Visitor extends KeywordsCompiler
{
    /**
     * @param Nodes\Node $node
     *
     * @return array
     */
    public function visit(Node $node)
    {
        $this->visitNode($node);

        return $this->buffer;
    }

    /**
     * @param Nodes\Node $node
     *
     * @return mixed
     */
    protected function visitNode(Node $node)
    {
        $fqn = get_class($node);
        $parts = explode('\\', $fqn);
        $name = strtolower(end($parts));
        $method = 'visit' . ucfirst($name);

        try {
            return $this->$method($node);
        } catch (\ErrorException $e) {
            if (!in_array($e->getCode(), array(8, 33))) {
                throw $e;
            }

            throw new \ErrorException(
                'Error on the ' . $name .
                (isset($node->name) ? ' "' . $node->name . '"' : '') .
                ($this->filename ? ' in ' . $this->filename : '') .
                ' line ' . $node->line . ":\n" . $e->getMessage(),
                34,
                1,
                __FILE__,
                __LINE__,
                $e
            );
        }
    }

    /**
     * @param Nodes\Literal $node
     */
    protected function visitLiteral(Literal $node)
    {
        $str = preg_replace('/\\n/', '\\\\n', $node->string);
        $this->buffer($str);
    }

    /**
     * @param Nodes\Block $block
     */
    protected function visitBlock(Block $block)
    {
        foreach ($block->nodes as $n) {
            $this->visit($n);
        }
    }

    /**
     * @param Nodes\Doctype $doctype
     *
     * @throws \Exception
     */
    protected function visitDoctype(Doctype $doctype = null)
    {
        $doc = (empty($doctype->value) || $doctype === null || !isset($doctype->value))
            ? 'default'
            : strtolower($doctype->value);

        $str = isset($this->doctypes[$doc])
            ? $this->doctypes[$doc]
            : "<!DOCTYPE {$doc}>";

        $this->buffer($str . $this->newline());

        $this->terse = (strtolower($str) === '<!doctype html>');

        $this->xml = ($doc === 'xml');
    }

    /**
     * @param Nodes\Mixin $mixin
     */
    protected function visitMixinBlock()
    {
        $name = var_export($this->visitedMixin->name, true);

        $code = $this->restrictedScope
            ? "\\Jade\\Compiler::callMixinBlock($name, \$attributes);"
            : "\$__varHandler = get_defined_vars(); \\Jade\\Compiler::callMixinBlockWithVars($name, \$__varHandler, \$attributes); extract(array_diff_key(\$__varHandler, array('__varHandler' => 1)));";

        $this->buffer($this->createCode($code));
    }

    /**
     * @param Nodes\Text $text
     */
    protected function visitText($text)
    {
        $this->buffer($this->interpolate($text->value));
    }

    /**
     * @param Nodes\Comment $comment
     */
    protected function visitComment(Comment $comment)
    {
        if ($comment->buffer) {
            $this->buffer('<!--' . $comment->value . '-->');
        }
    }

    /**
     * @param array $attributes
     */
    protected function visitAttributes($attributes)
    {
        $this->tempPrettyPrint(false, 'compileAttributes', $attributes);
    }
}
