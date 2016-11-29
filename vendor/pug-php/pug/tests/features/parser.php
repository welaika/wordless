<?php

class Lexer extends \Jade\Lexer
{
    public function nextToken()
    {
        static $i = 0;
        switch (++$i) {
            case 2:
                return $this->token('indent');
            case 4:
                return $this->token('text', 'Foo Bar');
            case 7:
                return $this->token('text', 'Hello');
            default:
                return $this->token('newline');
        }
    }
}

class Parser extends \Jade\Parser
{
    public function __construct($input, $filename = null, array $options = array())
    {
        parent::__construct($input, $filename, $options);

        $this->lexer = new Lexer($this->input, $this->options);
    }

    public function checkExtensions()
    {
        return $this->getExtensions();
    }

    public function testAccept($type)
    {
        return $this->accept($type);
    }
}

class JadeParserTest extends PHPUnit_Framework_TestCase
{
    public function testAccept()
    {
        $parser = new Parser('', 'file.jade', array());
        $this->assertSame($parser->testAccept('indent'), null, 'Should not get an indent at the first time');
        $parser->skip(1);
        $token = $parser->testAccept('indent');
        $this->assertSame($token->type, 'indent', 'Should get an indent at the second time');
        $parser->skip(1);
        $token = $parser->peek();
        $this->assertSame($token->type, 'text', 'The next token must be a text');
        $this->assertSame($token->value, 'Foo Bar', 'The next token must has the value Foo Bar');
        $parser->skip(3);
        $token = $parser->peek();
        $this->assertSame($token->type, 'text', 'The next token must be a text');
        $this->assertSame($token->value, 'Hello', 'The next token must has the value Hello');
    }

    public function testInclude()
    {
        $this->assertSame('<div class="alert alert-danger">Page not found.</div>', trim(get_php_code('include a/file/with/a.pug')));
        $this->assertSame('<div class="alert alert-danger">Page not found.</div>', trim(get_php_code('include a/file/with/an.extension')));
    }

    public function testExtensions()
    {
        $parser = new Parser('template', null, array(
            'extension' => '.pug',
        ));
        $this->assertSame(array('.pug'), $parser->checkExtensions());

        $parser = new Parser('template', null, array(
            'extension' => array('.pug'),
        ));
        $this->assertSame(array('.pug'), $parser->checkExtensions());

        $parser = new Parser('template', null, array(
            'extension' => array('.pug', '.jade'),
        ));
        $this->assertSame(array('.pug', '.jade'), $parser->checkExtensions());
    }
}
