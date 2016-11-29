<?php

use JsPhpize\Stream\ExpressionStream;

class StreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group stream
     */
    public function testStreamEmulator()
    {
        $stream = new ExpressionStream();

        $this->assertTrue($stream->stream_open('foo;bar'));
        $this->assertEmpty($stream->stream_stat());
        $this->assertSame('ba', $stream->stream_read(2));
        $this->assertSame(2, $stream->stream_tell());
        $this->assertFalse($stream->stream_eof());
        $this->assertTrue(is_array($stream->url_stat('foo', 0)));
        $this->assertSame('r', $stream->stream_read(2));
        $this->assertTrue($stream->stream_eof());
    }
}
