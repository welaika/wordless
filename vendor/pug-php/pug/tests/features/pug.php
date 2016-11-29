<?php

use Jade\Jade;
use Pug\Pug;

class PugAliasTest extends PHPUnit_Framework_TestCase
{
    /**
     * test the Pug alias
     */
    public function testPugAlias()
    {
        $jade = new Jade();
        $pug = new Pug();

        $this->assertSame($jade->getOption('stream'), 'jade.stream');
        $this->assertSame($pug->getOption('stream'), 'pug.stream');
        $this->assertSame($pug->render('p Hello'), '<p>Hello</p>');
        $this->assertSame($pug->getExtension(), '.pug');
        $this->assertTrue(in_array('.pug', $pug->getExtensions()));

        $jade = new Jade(array(
            'extension' => '.foo',
        ));
        $this->assertSame($jade->getExtension(), '.foo');
        $this->assertFalse(in_array('.pug', $jade->getExtensions()));
        $this->assertTrue(in_array('.foo', $jade->getExtensions()));

        $jade->setOption('extension', array('.jade', '.pug'));
        $this->assertSame($jade->getExtension(), '.jade');
        $this->assertFalse(in_array('.foo', $jade->getExtensions()));
        $this->assertTrue(in_array('.jade', $jade->getExtensions()));
        $this->assertTrue(in_array('.pug', $jade->getExtensions()));

        $jade->setOption('extension', array());
        $this->assertSame($jade->getExtension(), '');
        $this->assertFalse(in_array('', $jade->getExtensions()));
        $this->assertFalse(in_array('.foo', $jade->getExtensions()));
        $this->assertFalse(in_array('.jade', $jade->getExtensions()));
        $this->assertFalse(in_array('.pug', $jade->getExtensions()));

        $jade->setOption('extension', '.pug');
        $this->assertSame($jade->getExtension(), '.pug');
        $this->assertFalse(in_array('', $jade->getExtensions()));
        $this->assertFalse(in_array('.foo', $jade->getExtensions()));
        $this->assertFalse(in_array('.jade', $jade->getExtensions()));
        $this->assertTrue(in_array('.pug', $jade->getExtensions()));
    }
}
