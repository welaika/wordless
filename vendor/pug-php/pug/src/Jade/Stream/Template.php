<?php

namespace Jade\Stream;

/**
 * Creates a wrapper in order to allow the Zend PhpRenderer
 * to include the compiled file.
 * Class Jade\Stream\Template.
 */
class Template
{
    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var string
     */
    private $data = '';

    /**
     * @param $path
     *
     * @return bool
     */
    public function stream_open($path)
    {
        $this->data = substr(strstr($path, ';'), 1);

        return true;
    }

    /**
     * @return null
     */
    public function stream_stat()
    {
    }

    /**
     * @param $count
     *
     * @return string
     */
    public function stream_read($count)
    {
        $ret = substr($this->data, $this->position, $count);
        $this->position += strlen($ret);

        return $ret;
    }

    /**
     * @return int
     */
    public function stream_tell()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function stream_eof()
    {
        return $this->position >= strlen($this->data);
    }

    /**
     * Dummy URL stat method to prevent PHP "undefined method" errors.
     *
     * @return array
     */
    public function url_stat($path, $flags)
    {
        return array(0, 0, 0, 0, 0, 0, 0, strlen($this->data), 0, 0, 0, 0);
    }
}
