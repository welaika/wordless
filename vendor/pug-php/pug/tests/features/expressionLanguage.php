<?php

use Jade\Compiler;
use Jade\Jade;

class ExpressionCompilerTester extends Compiler
{
    public function callPhpizeExpression()
    {
        return call_user_func_array(array($this, 'phpizeExpression'), func_get_args());
    }
}

class JadeExpressionLanguageTest extends PHPUnit_Framework_TestCase
{
    public function testJsExpression()
    {
        $jade = new Jade(array(
            'singleQuote' => false,
            'expressionLanguage' => 'js',
        ));

        $actual = trim($jade->render("- a = 2\n- b = 4\n- c = b * a\np=a * b\np=c"));
        $this->assertSame('<p>8</p><p>8</p>', $actual);

        $actual = trim($jade->render("- a = 2\n- b = 4\np=a + b"));
        $this->assertSame('<p>6</p>', $actual);

        $actual = trim($jade->render("- a = '2'\n- b = 4\np=a + b"));
        $this->assertSame('<p>24</p>', $actual);

        $compiler = new ExpressionCompilerTester(array(
            'expressionLanguage' => 'js',
        ));
        $actual = trim($compiler->callPhpizeExpression('addDollarIfNeeded', 'array(1)'));
        $this->assertSame('array(1)', $actual);
        $actual = trim($compiler->callPhpizeExpression('addDollarIfNeeded', 'a'));
        $this->assertSame('$a', $actual);
    }

    public function testPhpExpression()
    {
        $compiler = new ExpressionCompilerTester(array(
            'expressionLanguage' => 'php',
        ));

        $actual = trim($compiler->callPhpizeExpression('addDollarIfNeeded', 'a'));
        $this->assertSame('a', $actual);
    }

    public function testAutoExpression()
    {
        $compiler = new ExpressionCompilerTester(array(
            'expressionLanguage' => 3.123,
        ));

        $actual = trim($compiler->callPhpizeExpression('addDollarIfNeeded', 'a'));
        $this->assertSame('$a', $actual);
    }
}
