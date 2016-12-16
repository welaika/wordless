<?php

use Jade\Stream\Template;

class JadeStreamTest extends PHPUnit_Framework_TestCase
{
    protected $stream;

    /**
     * Test stream methods.
     */
    public function testStreamMethods()
    {
        $this->stream = new Template();
        $this->assertTrue($this->stream->stream_open('data:text/plain;base64,foobar'));
        $this->assertTrue(is_array($this->stream->url_stat('/foo', 1)));
        $this->assertSame($this->stream->stream_read(6), 'base64');
        $this->assertSame($this->stream->stream_tell(), 6);
        $this->assertSame($this->stream->stream_read(4), ',foo');
        $this->assertSame($this->stream->stream_tell(), 10);
    }
}
