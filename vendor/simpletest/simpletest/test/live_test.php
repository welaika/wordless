<?php

require_once __DIR__ . '/../autorun.php';
require_once __DIR__ . '/../socket.php';
require_once __DIR__ . '/../http.php';
require_once __DIR__ . '/../compatibility.php';

if (SimpleTest::getDefaultProxy()) {
    SimpleTest::ignore('LiveHttpTestCase');
}

class LiveHttpTestCase extends UnitTestCase
{
    protected $host = 'localhost';
    protected $port = '8080';

    function skip()
    {
        $socket = new SimpleSocket($this->host, $this->port, 15, 8);

        parent::skipIf(
            ! $socket->isOpen(),
            sprintf('The LiveHttpTestCase requires that a webserver runs at %s:%s', $this->host, $this->port)
        );
    }

    public function testBadSocket()
    {
        $socket = new SimpleSocket('bad_url', 111, 5);
        $this->assertTrue($socket->isError());
        $this->assertPattern(
                '/Cannot open \\[bad_url:111\\] with \\[/',
                $socket->getError());
        $this->assertFalse($socket->isOpen());
        $this->assertFalse($socket->write('A message'));
    }

    public function testSocketClosure()
    {
        $socket = new SimpleSocket($this->host, $this->port, 15, 8);
        $this->assertTrue($socket->isOpen());
        $this->assertTrue($socket->write("GET /network_confirm.php HTTP/1.0\r\n"));
        $socket->write("Host: $this->host\r\n");
        $socket->write("Connection: close\r\n\r\n");
        $this->assertEqual($socket->read(), 'HTTP/1.0');
        $socket->close();
        $this->assertIdentical($socket->read(), false);
    }

    public function testRecordOfSentCharacters()
    {
        $socket = new SimpleSocket($this->host, $this->port, 15);
        $this->assertTrue($socket->write("GET /network_confirm.php HTTP/1.0\r\n"));
        $socket->write("Host: $this->host\r\n");
        $socket->write("Connection: close\r\n\r\n");
        $socket->close();
        $this->assertEqual($socket->getSent(),
                "GET /network_confirm.php HTTP/1.0\r\n" .
                "Host: $this->host\r\n" .
                "Connection: close\r\n\r\n");
    }
}
