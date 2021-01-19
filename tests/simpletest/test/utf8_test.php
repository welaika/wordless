<?php
// Handle with care : this file is UTF8.

require_once __DIR__ . '/../autorun.php';
require_once __DIR__ . '/../php_parser.php';
require_once __DIR__ . '/../url.php';

Mock::generate('SimpleHtmlSaxParser');
Mock::generate('SimplePhpPageBuilder');

class TestOfHtmlSaxParserWithDifferentCharset extends UnitTestCase
{
    public function testWithTextInUTF8()
    {
        $regex = new ParallelRegex(false);
        $regex->addPattern('eé');
        $this->assertTrue($regex->match('eéêè', $match));
        $this->assertEqual($match, 'eé');
    }

    public function testWithTextInLatin1()
    {
        $regex = new ParallelRegex(false);
        $regex->addPattern(utf8_decode('eé'));
        $this->assertTrue($regex->match(utf8_decode('eéêè'), $match));
        $this->assertEqual($match, utf8_decode('eé'));
    }

    public function createParser()
    {
        $parser = new MockSimpleHtmlSaxParser();
        $parser->returnsByValue('acceptStartToken', true);
        $parser->returnsByValue('acceptEndToken', true);
        $parser->returnsByValue('acceptAttributeToken', true);
        $parser->returnsByValue('acceptEntityToken', true);
        $parser->returnsByValue('acceptTextToken', true);
        $parser->returnsByValue('ignore', true);

        return $parser;
    }

    public function testTagWithAttributesInUTF8()
    {
        $parser = $this->createParser();
        $parser->expectOnce('acceptTextToken', array('label', '*'));
        $parser->expectAt(0, 'acceptStartToken', array('<a', '*'));
        $parser->expectAt(1, 'acceptStartToken', array('href', '*'));
        $parser->expectAt(2, 'acceptStartToken', array('>', '*'));
        $parser->expectCallCount('acceptStartToken', 3);
        $parser->expectAt(0, 'acceptAttributeToken', array('= "', '*'));
        $parser->expectAt(1, 'acceptAttributeToken', array('hère.html', '*'));
        $parser->expectAt(2, 'acceptAttributeToken', array('"', '*'));
        $parser->expectCallCount('acceptAttributeToken', 3);
        $parser->expectOnce('acceptEndToken', array('</a>', '*'));
        $lexer = new SimpleHtmlLexer($parser);
        $this->assertTrue($lexer->parse('<a href = "hère.html">label</a>'));
    }

    public function testTagWithAttributesInLatin1()
    {
        $parser = $this->createParser();
        $parser->expectOnce('acceptTextToken', array('label', '*'));
        $parser->expectAt(0, 'acceptStartToken', array('<a', '*'));
        $parser->expectAt(1, 'acceptStartToken', array('href', '*'));
        $parser->expectAt(2, 'acceptStartToken', array('>', '*'));
        $parser->expectCallCount('acceptStartToken', 3);
        $parser->expectAt(0, 'acceptAttributeToken', array('= "', '*'));
        $parser->expectAt(1, 'acceptAttributeToken', array(utf8_decode('hère.html'), '*'));
        $parser->expectAt(2, 'acceptAttributeToken', array('"', '*'));
        $parser->expectCallCount('acceptAttributeToken', 3);
        $parser->expectOnce('acceptEndToken', array('</a>', '*'));
        $lexer = new SimpleHtmlLexer($parser);
        $this->assertTrue($lexer->parse(utf8_decode('<a href = "hère.html">label</a>')));
    }
}

class TestOfUrlithDifferentCharset extends UnitTestCase
{
    public function testUsernameAndPasswordInUTF8()
    {
        $url = new SimpleUrl('http://pÈrick:penËt@www.lastcraft.com');
        $this->assertEqual($url->getUsername(), 'pÈrick');
        $this->assertEqual($url->getPassword(), 'penËt');
    }
}
