<?php

use Jade\Compiler;

class Decoder extends Compiler
{
    public function decode($attributes)
    {
        return static::decodeAttributes($attributes);
    }
}

class CompilerUtilsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Attributes decoding test
     */
    public function testDecodeAttributes()
    {
        $decoder = new Decoder();
        $attributes = $decoder->decode(array(
            '"Hello"',
            array(
                'name' => 'href',
                'value' => '/',
            ),
            array(
                'name' => 'data-id',
                'value' => 34,
            ),
            array(
                'name' => 'first-name',
                'value' => "'Bob'",
            ),
            array(
                'name' => 'last-name',
                'value' => '"Dylan"',
            ),
            array(
                'name' => 'checked',
                'value' => true,
            ),
        ));
        $this->assertSame($attributes[0], 'Hello');
        $this->assertSame($attributes[1]['value'], '/');
        $this->assertSame($attributes[2]['value'], 34);
        $this->assertSame($attributes[3]['value'], 'Bob');
        $this->assertSame($attributes[4]['value'], 'Dylan');
        $this->assertSame($attributes[5]['value'], true);
    }

    public function testWithMixinAttributes()
    {
        $attributes = Compiler::withMixinAttributes(array(
            'a' => 'b',
        ), array(
            array(
                'name' => 'class',
                'value' => 'foo',
            )
        ));

        $this->assertSame(array(
            'a' => 'b',
            'class' => 'foo',
        ), $attributes);
    }
}
