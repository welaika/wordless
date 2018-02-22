<?php

namespace Phug\Util;

use ErrorException;
use Exception;
use Throwable;

class SandBox
{
    /**
     * @var mixed
     */
    private $result;

    /**
     * @var string
     */
    private $buffer;

    /**
     * @var Throwable
     */
    private $throwable;

    public function __construct(callable $action, callable $errorInterceptor = null)
    {
        set_error_handler($errorInterceptor ?: function ($number, $message, $file, $line) {
            if (error_reporting() & $number) {
                throw new ErrorException($message, 0, $number, $file, $line);
            }

            return false;
        });
        ob_start();
        // @codeCoverageIgnoreStart
        try {
            $this->result = $action();
        } catch (Throwable $throwable) { // PHP 7
            $this->throwable = $throwable;
        } catch (Exception $exception) { // PHP 5
            $this->throwable = $exception;
        }
        // @codeCoverageIgnoreEnd
        $this->buffer = ob_get_contents();
        ob_end_clean();
        restore_error_handler();
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return Throwable
     */
    public function getThrowable()
    {
        return $this->throwable;
    }

    /**
     * @return string
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    public function outputBuffer()
    {
        echo $this->buffer;

        $this->buffer = '';
    }
}
