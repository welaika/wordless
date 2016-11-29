<?php

use Jade\Nodes\Attributes;

class AttributesTest extends PHPUnit_Framework_TestCase
{
    /**
     * Attributes Node test
     */
    public function testAttributes()
    {
        $attributes = new Attributes();
        $this->assertTrue(is_null($attributes->getAttribute('foo')), 'First, foo attribute should not exists');
        $attributes->setAttribute('foo', 'bar');
        $foo = $attributes->getAttribute('foo');
        $this->assertSame($foo['value'], 'bar', 'Then, foo attribute should be bar');
        $this->assertFalse($foo['escaped'], 'And foo should not be escaped');
        $attributes->removeAttribute('foo', 'bar');
        $this->assertTrue(is_null($attributes->getAttribute('foo')), 'First, foo attribute should be removed');
    }
}
