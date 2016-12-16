<?php

use Jade\Compiler;

class JadeCompilerHandleCodeTest extends PHPUnit_Framework_TestCase
{
    public function testGoodClosing()
    {
        $compiler = new Compiler();
        $this->assertTrue(is_array($compiler->handleCode('$a = [$b, $e]')));
    }

    public function testNestedParentheses()
    {
        $compiler = new Compiler();
        $code = $compiler->handleCode('b->c(d->e->f, g->h)');
        $this->assertSame('$__=$b->c($d->e->f, $g->h)', $code[0]);
    }

    public function testNestedParenthesesCount()
    {
        $compiler = new Compiler();
        $code = $compiler->handleCode('b->c(a(d->e->f), g->h)');
        $this->assertSame(3, count($code));
    }
}
