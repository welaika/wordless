<?php

use Jade\Compiler;
use Jade\Jade;

class JadeCompilerOptionsTest extends PHPUnit_Framework_TestCase
{
    public function testArrayOptions()
    {
        $compiler = new Compiler(array(
            'allowMixinOverride' => true,
            'indentChar' => '-',
        ));
        $this->assertTrue($compiler->getOption('allowMixinOverride'));
        $this->assertSame('-', $compiler->getOption('indentChar'));
    }

    public function testEngineOptions()
    {
        $jade = new Jade(array(
            'terse' => false,
            'indentChar' => '@',
        ));
        $compiler = new Compiler($jade);
        $jade->setCustomOption('foo', 'bar');
        $this->assertFalse($compiler->getOption('terse'));
        $this->assertSame('@', $compiler->getOption('indentChar'));
        $this->assertSame('bar', $compiler->getOption('foo'));
    }
}
