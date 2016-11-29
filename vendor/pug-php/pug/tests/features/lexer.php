<?php

use Jade\Lexer;

class BrokenLexer extends Lexer
{
    public function scanText()
    {
        return $this->scan('/nothing/', 'text');
    }
}

class LexerTest extends PHPUnit_Framework_TestCase
{
    public function testNothingFound()
    {
        $lexer = new BrokenLexer('| Some text');
        $this->assertSame(null, $lexer->nextToken(), 'Should not find text.');
    }
}
