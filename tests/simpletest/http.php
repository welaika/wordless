<?php

require_once __DIR__ . '/socket.php';
require_once __DIR__ . '/cookies.php';
require_once __DIR__ . '/url.php';

/**
 * Creates HTTP headers for the end point of a HTTP request.
 */
class SimpleRoute
{
    private $url;

    /**
     * Sets the target URL.
     *
     * @param SimpleUrl $url   URL as object.
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Resource name.
     *
     * @return SimpleUrl        Current url.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Creates the first line which is the actual request.
     *
     * @param string $method   HTTP request method, usually GET.
     *
     * @return string          Request line content.
     */
    protected function getRequestLine($method)
    {
        return $method . ' ' . $this->url->getPath() .
                $this->url->getEncodedRequest() . ' HTTP/1.0';
    }

    /**
     * Creates the host part of the request.
     *
     * @return string          Host line content.
     */
    protected function getHostLine()
    {
        $line = 'Host: ' . $this->url->getHost();
        if ($this->url->getPort()) {
            $line .= ':' . $this->url->getPort();
        }

        return $line;
    }

    /**
     * Opens a socket to the route.
     *
     * @param string $method      HTTP request method, usually GET.
     * @param int $timeout    Connection timeout.
     *
     * @return SimpleSocket       New socket.
     */
    public function createConnection($method, $timeout)
    {
        $default_port = ('https' === $this->url->getScheme()) ? 443 : 80;
        $socket       = $this->createSocket(
                $this->url->getScheme() ? $this->url->getScheme() : 'http',
                $this->url->getHost(),
                $this->url->getPort() ? $this->url->getPort() : $default_port,
                $timeout);
        if (! $socket->isError()) {
            $socket->write($this->getRequestLine($method) . "\r\n");
            $socket->write($this->getHostLine() . "\r\n");
            $socket->write("Connection: close\r\n");
        }

        return $socket;
    }

    /**
     * Factory for socket.
     *
     * @param string $scheme                   Protocol to use.
     * @param string $host                     Hostname to connect to.
     * @param int $port                    Remote port.
     * @param int $timeout                 Connection timeout.
     *
     * @return SimpleSocket/SimpleSecureSocket New socket.
     */
    protected function createSocket($scheme, $host, $port, $timeout)
    {
        if (in_array($scheme, array('file'))) {
            return new SimpleFileSocket($this->url);
        } elseif (in_array($scheme, array('https'))) {
            return new SimpleSecureSocket($host, $port, $timeout);
        } else {
            return new SimpleSocket($host, $port, $timeout);
        }
    }
}

/**
 * Creates HTTP headers for the end point of a HTTP request via a proxy server.
 */
class SimpleProxyRoute extends SimpleRoute
{
    private $password;
    private $proxy;
    private $username;

    /**
     * Stashes the proxy address.
     *
     * @param SimpleUrl $url     URL as object.
     * @param string $proxy      Proxy URL.
     * @param string $username   Username for autentication.
     * @param string $password   Password for autentication.
     */
    public function __construct($url, $proxy, $username = false, $password = false)
    {
        parent::__construct($url);
        $this->password = $password;
        $this->proxy    = $proxy;
        $this->username = $username;
    }

    /**
     * Creates the first line which is the actual request.
     *
     * @param string $method   HTTP request method, usually GET.
     * @param SimpleUrl $url   URL as object.
     *
     * @return string          Request line content.
     */
    public function getRequestLine($method)
    {
        $url    = $this->getUrl();
        $scheme = $url->getScheme() ? $url->getScheme() : 'http';
        $port   = $url->getPort() ? ':' . $url->getPort() : '';

        return $method . ' ' . $scheme . '://' . $url->getHost() . $port .
                $url->getPath() . $url->getEncodedRequest() . ' HTTP/1.0';
    }

    /**
     * Creates the host part of the request.
     *
     * @param SimpleUrl $url   URL as object.
     *
     * @return string          Host line content.
     */
    public function getHostLine()
    {
        $host = 'Host: ' . $this->proxy->getHost();
        $port = $this->proxy->getPort() ? $this->proxy->getPort() : 8080;

        return "$host:$port";
    }

    /**
     * Opens a socket to the route.
     *
     * @param string $method       HTTP request method, usually GET.
     * @param int $timeout     Connection timeout.
     *
     * @return SimpleSocket        New socket.
     */
    public function createConnection($method, $timeout)
    {
        $socket = $this->createSocket(
                $this->proxy->getScheme() ? $this->proxy->getScheme() : 'http',
                $this->proxy->getHost(),
                $this->proxy->getPort() ? $this->proxy->getPort() : 8080,
                $timeout);
        if ($socket->isError()) {
            return $socket;
        }
        $socket->write($this->getRequestLine($method) . "\r\n");
        $socket->write($this->getHostLine() . "\r\n");
        if ($this->username && $this->password) {
            $socket->write('Proxy-Authorization: Basic ' .
                    base64_encode($this->username . ':' . $this->password) .
                    "\r\n");
        }
        $socket->write("Connection: close\r\n");

        return $socket;
    }
}

/**
 * HTTP request for a web page. Factory for HttpResponse object.
 */
class SimpleHttpRequest
{
    private $cookies;
    private $encoding;
    private $headers;
    private $route;

    /**
     * Builds the socket request from the different pieces.
     * These include proxy information, URL,
     * cookies, headers, request method and choice of encoding.
     *
     * @param SimpleRoute $route              Request route.
     * @param SimpleFormEncoding $encoding    Content to send with request.
     */
    public function __construct($route, $encoding)
    {
        $this->cookies  = array();
        $this->encoding = $encoding;
        $this->headers  = array();
        $this->route    = $route;
    }

    /**
     * Dispatches the content to the route's socket.
     *
     * @param int $timeout      Connection timeout.
     *
     * @return SimpleHttpResponse   A response which may only have an error, but hopefully has a
     * complete web page.
     */
    public function fetch($timeout)
    {
        $socket = $this->route->createConnection($this->encoding->getMethod(), $timeout);
        if (! $socket->isError()) {
            $this->dispatchRequest($socket, $this->encoding);
        }

        return $this->createResponse($socket);
    }

    /**
     * Sends the headers.
     *
     * @param SimpleSocket $socket           Open socket.
     * @param string $method                 HTTP request method, usually GET.
     * @param SimpleFormEncoding $encoding   Content to send with request.
     */
    protected function dispatchRequest($socket, $encoding)
    {
        foreach ($this->headers as $header_line) {
            $socket->write($header_line . "\r\n");
        }
        if (count($this->cookies) > 0) {
            $socket->write('Cookie: ' . implode(';', $this->cookies) . "\r\n");
        }
        $encoding->writeHeadersTo($socket);
        $socket->write("\r\n");
        $encoding->writeTo($socket);
    }

    /**
     * Get the headers to be sent with the request.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     *    Adds a header line to the request.
     *
     *    @param string $header_line    Text of full header line.
     */
    public function addHeaderLine($header_line)
    {
        $this->headers[] = $header_line;
    }

    /**
     * Reads all the relevant cookies from the cookie jar.
     *
     * @param SimpleCookieJar $jar     Jar to read
     * @param SimpleUrl $url           Url to use for scope.
     */
    public function readCookiesFromJar($jar, $url)
    {
        $this->cookies = $jar->selectAsPairs($url);
    }

    /**
     * Wraps the socket in a response parser.
     *
     * @param SimpleSocket $socket   Responding socket.
     *
     * @return SimpleHttpResponse    Parsed response object.
     */
    protected function createResponse($socket)
    {
        $response = new SimpleHttpResponse(
                $socket,
                $this->route->getUrl(),
                $this->encoding);
        $socket->close();

        return $response;
    }
}

/**
 * Collection of header lines in the response.
 */
class SimpleHttpHeaders
{
    private $authentication;
    private $cookies;
    private $http_version;
    private $location;
    private $mime_type;
    private $raw_headers;
    private $realm;
    private $response_code;

    /**
     * Parses the incoming header block.
     *
     * @param string $headers     Header block.
     */
    public function __construct($headers)
    {
        $this->authentication = false;
        $this->cookies        = array();
        $this->http_version   = false;
        $this->location       = false;
        $this->mime_type      = '';
        $this->raw_headers    = $headers;
        $this->realm          = false;
        $this->response_code  = false;
        foreach (explode("\r\n", $headers) as $header_line) {
            $this->parseHeaderLine($header_line);
        }
    }

    /**
     * Accessor for parsed HTTP protocol version.
     *
     * @return int           HTTP error code.
     */
    public function getHttpVersion()
    {
        return $this->http_version;
    }

    /**
     * Accessor for raw header block.
     *
     * @return string        All headers as raw string.
     */
    public function getRaw()
    {
        return $this->raw_headers;
    }

    /**
     * Accessor for parsed HTTP error code.
     *
     * @return int           HTTP error code.
     */
    public function getResponseCode()
    {
        return (integer) $this->response_code;
    }

    /**
     * Returns the redirected URL or false if no redirection.
     *
     * @return string      URL or false for none.
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Test to see if the response is a valid redirect.
     *
     * @return bool       True if valid redirect.
     */
    public function isRedirect()
    {
        return in_array($this->response_code, array(301, 302, 303, 307)) &&
                (boolean) $this->getLocation();
    }

    /**
     * Test to see if the response is an authentication challenge.
     *
     * @return bool       True if challenge.
     */
    public function isChallenge()
    {
        return ($this->response_code == 401) &&
                (boolean) $this->authentication &&
                (boolean) $this->realm;
    }

    /**
     * Accessor for MIME type header information.
     *
     * @return string           MIME type.
     */
    public function getMimeType()
    {
        return $this->mime_type;
    }

    /**
     * Accessor for authentication type.
     *
     * @return string        Type.
     */
    public function getAuthentication()
    {
        return $this->authentication;
    }

    /**
     * Accessor for security realm.
     *
     * @return string        Realm.
     */
    public function getRealm()
    {
        return $this->realm;
    }

    /**
     * Writes new cookies to the cookie jar.
     *
     * @param SimpleCookieJar $jar   Jar to write to.
     * @param SimpleUrl $url         Host and path to write under.
     */
    public function writeCookiesToJar($jar, $url)
    {
        foreach ($this->cookies as $cookie) {
            $jar->setCookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $url->getHost(),
                    $cookie->getPath(),
                    $cookie->getExpiry());
        }
    }

    /**
     * Called on each header line to accumulate the held data within the class.
     *
     * @param string $header_line        One line of header.
     */
    protected function parseHeaderLine($header_line)
    {
        if (preg_match('/HTTP\/(\d+\.\d+)\s+(\d+)/i', $header_line, $matches)) {
            $this->http_version  = $matches[1];
            $this->response_code = $matches[2];
        }
        if (preg_match('/Content-type:\s*(.*)/i', $header_line, $matches)) {
            $this->mime_type = trim($matches[1]);
        }
        if (preg_match('/Location:\s*(.*)/i', $header_line, $matches)) {
            $this->location = trim($matches[1]);
        }
        if (preg_match('/Set-cookie:(.*)/i', $header_line, $matches)) {
            $this->cookies[] = $this->parseCookie($matches[1]);
        }
        if (preg_match('/WWW-Authenticate:\s+(\S+)\s+realm=\"(.*?)\"/i', $header_line, $matches)) {
            $this->authentication = $matches[1];
            $this->realm          = trim($matches[2]);
        }
    }

    /**
     * Parse the Set-cookie content.
     *
     * @param string $cookie_line    Text after "Set-cookie:"
     *
     * @return SimpleCookie          New cookie object.
     */
    protected function parseCookie($cookie_line)
    {
        $parts  = explode(';', $cookie_line);
        $cookie = array();
        preg_match('/\s*(.*?)\s*=(.*)/', array_shift($parts), $cookie);
        foreach ($parts as $part) {
            if (preg_match('/\s*(.*?)\s*=(.*)/', $part, $matches)) {
                $cookie[$matches[1]] = trim($matches[2]);
            }
        }

        return new SimpleCookie(
                $cookie[1],
                trim($cookie[2]),
                isset($cookie['path']) ? $cookie['path'] : '',
                isset($cookie['expires']) ? $cookie['expires'] : false);
    }
}

/**
 * Basic HTTP response.
 */
class SimpleHttpResponse extends SimpleStickyError
{
    private $url;
    private $encoding;
    private $sent;
    private $content;
    private $headers;

    /**
     * Constructor. Reads and parses the incoming content and headers.
     *
     * @param SimpleSocket $socket   Network connection to fetch response text from.
     * @param SimpleUrl $url         Resource name.
     * @param mixed $encoding        Record of content sent.
     */
    public function __construct($socket, $url, $encoding)
    {
        parent::__construct();
        $this->url      = $url;
        $this->encoding = $encoding;
        $this->sent     = $socket->getSent();
        $this->content  = false;
        $raw            = $this->readAll($socket);
        if ($socket->isError()) {
            $this->setError('Error reading socket [' . $socket->getError() . ']');

            return;
        }
        $this->parse($raw);
    }

    /**
     * Splits up the headers and the rest of the content.
     *
     * @param string $raw    Content to parse.
     */
    protected function parse($raw)
    {
        if (! $raw) {
            $this->setError('Nothing fetched');
            $this->headers = new SimpleHttpHeaders('');
        } elseif ('file' === $this->url->getScheme()) {
            $this->headers = new SimpleHttpHeaders('');
            $this->content = $raw;
        } elseif (! strstr($raw, "\r\n\r\n")) {
            $this->setError('Could not split headers from content');
            $this->headers = new SimpleHttpHeaders($raw);
        } else {
            list($headers, $this->content) = explode("\r\n\r\n", $raw, 2);
            $this->headers                 = new SimpleHttpHeaders($headers);
        }
    }

    /**
     * Original request method.
     *
     * @return string        GET, POST or HEAD.
     */
    public function getMethod()
    {
        return $this->encoding->getMethod();
    }

    /**
     * Resource name.
     *
     * @return SimpleUrl        Current url.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Original request data.
     *
     * @return mixed              Sent content.
     */
    public function getRequestData()
    {
        return $this->encoding;
    }

    /**
     * Raw request that was sent down the wire.
     *
     * @return string        Bytes actually sent.
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * Accessor for the content after the last header line.
     *
     * @return string           All content.
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Accessor for header block. The response is the combination of this and the content.
     *
     * @return SimpleHttpHeaders        Wrapped header block.
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Accessor for any new cookies.
     *
     * @return array       List of new cookies.
     */
    public function getNewCookies()
    {
        return $this->headers->getNewCookies();
    }

    /**
     * Reads the whole of the socket output into a single string.
     *
     * @param SimpleSocket $socket  Unread socket.
     *
     * @return string               Raw output if successful else false.
     */
    protected function readAll($socket)
    {
        $all = '';
        while (! $this->isLastPacket($next = $socket->read())) {
            $all .= $next;
        }

        return $all;
    }

    /**
     * Test to see if the packet from the socket is the last one.
     *
     * @param string $packet    Chunk to interpret.
     *
     * @return bool          True if empty or EOF.
     */
    protected function isLastPacket($packet)
    {
        if (is_string($packet)) {
            return $packet === '';
        }

        return ! $packet;
    }
}
