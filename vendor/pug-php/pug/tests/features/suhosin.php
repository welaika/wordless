<?php

use Pug\Pug;

class PugSuhosinBorken extends Pug
{
    protected function whiteListNeeded($extension)
    {
        return true;
    }
}

class PugSuhosinEmulation extends Pug
{
    public function requirements($name = null)
    {
        $extensions = get_loaded_extensions();
        $requirements = array(
            'streamWhiteListed' => !$this->whiteListNeeded($extensions[0]),
            'cacheFolderExists' => $this->options['cache'] && is_dir($this->options['cache']),
            'cacheFolderIsWritable' => $this->options['cache'] && is_writable($this->options['cache']),
        );

        if ($name) {
            if (!isset($requirements[$name])) {
                throw new \InvalidArgumentException($name . ' is not in the requirements list (' . implode(', ', array_keys($requirements)) . ')', 19);
            }

            return $requirements[$name];
        }

        return $requirements;
    }

    public function stream($input)
    {
        $extensions = get_loaded_extensions();
        if ($this->whiteListNeeded($extensions[0])) {
            throw new \ErrorException('To run Pug.php on the fly, add "' . $this->options['stream'] . '" to the "suhosin.executor.include.whitelist" settings in your php.ini file.', 4);
        }

        if (!in_array($this->options['stream'], static::$wrappersRegistered)) {
            static::$wrappersRegistered[] = $this->options['stream'];
            stream_wrapper_register($this->options['stream'], 'Jade\Stream\Template');
        }

        return $this->options['stream'] . '://data;' . $input;
    }
}

class JadeSuhosinTest extends PHPUnit_Framework_TestCase
{
    public function testSuhosinBroken()
    {
        $pug = new PugSuhosinBorken();
        $message = '';
        $code = null;
        try {
            $pug->render('h1 Hello');
        } catch (\ErrorException $e) {
            $message = $e->getMessage();
            $code = $e->getCode();
        }
        $this->assertTrue(false !== strpos($message, 'suhosin.executor.include.whitelist'), 'Error should contain suhosin.executor.include.whitelist.');
        $this->assertTrue(false !== strpos($message, 'pug.stream'), 'Error should contain pug.stream.');
        $this->assertSame(4, $code, 'The error code should be 4');
    }

    public function testSuhosinEnabled()
    {
        $extensions = get_loaded_extensions();
        $pug = new PugSuhosinEmulation();
        if (ini_get($extensions[0] . '.executor.include.whitelist')) {
            ini_set($extensions[0] . '.executor.include.whitelist', '');
        }
        $this->assertFalse($pug->requirements('streamWhiteListed'), 'Stream should not be white listed');
        $message = '';
        $code = null;
        try {
            $pug->render('h1 Hello');
        } catch (\ErrorException $e) {
            $message = $e->getMessage();
            $code = $e->getCode();
        }
        $this->assertTrue(false !== strpos($message, 'suhosin.executor.include.whitelist'), 'Error should contain suhosin.executor.include.whitelist.');
        $this->assertTrue(false !== strpos($message, 'pug.stream'), 'Error should contain pug.stream.');
        $this->assertSame(4, $code, 'The error code should be 4');
    }
}
