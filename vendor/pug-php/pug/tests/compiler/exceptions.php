<?php

use Jade\Compiler;
use Jade\Jade;

class StatementsBugCompiler extends Compiler
{
    public function __construct()
    {
        $this->createStatements();
    }
}

class ApplyBugCompiler extends Compiler
{
    public function __construct()
    {
        $this->apply('foo', array());
    }
}

class JadeCompilerExceptionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 12
     */
    public function testHandleEmptyCode()
    {
        $compiler = new Compiler();
        $compiler->handleCode('');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 11
     */
    public function testNonStringInHandleCode()
    {
        $compiler = new Compiler();
        $compiler->handleCode(array());
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 14
     */
    public function testMissingClosing()
    {
        $compiler = new Compiler();
        $compiler->handleCode('$a = [$b, c(d$e]');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 9
     */
    public function testCreateEmptyStatement()
    {
        new StatementsBugCompiler();
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionCode 7
     */
    public function testBadMethodApply()
    {
        new ApplyBugCompiler();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 28
     */
    public function testInvalidOption()
    {
        $compiler = new Compiler();
        $compiler->getOption('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 2
     */
    public function testInvalidOptionWithEngineInConstructor()
    {
        $jade = new Jade();
        $compiler = new Compiler($jade);
        $compiler->getOption('foo');
    }
}
