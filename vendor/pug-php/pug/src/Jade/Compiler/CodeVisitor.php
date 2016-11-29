<?php

namespace Jade\Compiler;

use Jade\Nodes\Code;

abstract class CodeVisitor extends TagVisitor
{
    /**
     * @param Nodes\Code $node
     */
    protected function visitCodeConditional(array $matches)
    {
        $code = trim($matches[2], '; ');
        while (($len = strlen($code)) > 1 && ($code[0] === '(' || $code[0] === '{') && ord($code[0]) === ord(substr($code, -1)) - 1) {
            $code = trim(substr($code, 1, $len - 2));
        }

        $index = count($this->buffer) - 1;
        $conditional = '';

        if (isset($this->buffer[$index]) && false !== strpos($this->buffer[$index], $this->createCode('}'))) {
            // the "else" statement needs to be in the php block that closes the if
            $this->buffer[$index] = null;
            $conditional .= '} ';
        }

        $conditional .= '%s';

        if (strlen($code) > 0) {
            $conditional .= '(%s) {';
            $conditional = $matches[1] === 'unless'
                ? sprintf($conditional, 'if', '!(%s)')
                : sprintf($conditional, $matches[1], '%s');
            $this->buffer($this->createCode($conditional, $code));

            return;
        }

        $conditional .= ' {';
        $conditional = sprintf($conditional, $matches[1]);

        $this->buffer($this->createCode($conditional));
    }

    /**
     * @param Nodes\Code $node
     */
    protected function visitCodeOpening(Code $node)
    {
        $code = trim($node->value);

        if ($node->buffer) {
            $this->buffer($this->escapeIfNeeded($node->escape, $code));

            return;
        }

        $phpOpen = implode('|', $this->phpOpenBlock);

        if (preg_match("/^[[:space:]]*({$phpOpen})(.*)/", $code, $matches)) {
            $this->visitCodeConditional($matches);

            return;
        }

        $this->buffer($this->createCode('%s', $code));
    }

    /**
     * @param Nodes\Code $node
     */
    protected function visitCode(Code $node)
    {
        $this->visitCodeOpening($node);

        if (isset($node->block)) {
            $this->indents++;
            $this->visit($node->block);
            $this->indents--;

            if (!$node->buffer) {
                $this->buffer($this->createCode('}'));
            }
        }
    }
}
