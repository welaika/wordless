<?php

use Pug\Pug;

/**
 * Test server example
 */
class PugExampleTest extends PHPUnit_Framework_TestCase
{
    protected function simpleHtml($contents)
    {
        return preg_replace('/\s+/', ' ', trim($contents));
    }

    protected function browse($path = '')
    {
        if (!defined('PHP_BINARY')) {
            define('PHP_BINARY', 'php');
        }

        return $this->simpleHtml(shell_exec(trim(
            PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/../../example/index.php') . ' ' . $path
        )));
    }

    protected function getRenderedFile($file)
    {
        return $this->simpleHtml(file_get_contents(__DIR__ . '/render/' . $file . '.html'));
    }

    /**
     * Test server example index
     */
    public function testIndex()
    {
        $this->assertSame($this->browse(), $this->getRenderedFile('index'));
    }

    /**
     * Test server example login page
     */
    public function testLogin()
    {
        $this->assertSame($this->browse('login'), $this->getRenderedFile('login'));
    }

    /**
     * Test server example cities page
     */
    public function testCities()
    {
        $this->assertSame($this->browse('cities'), $this->getRenderedFile('cities'));
    }
}
