<?php

use Jade\Jade;

class ShareTest extends PHPUnit_Framework_TestCase
{
    public function testShare()
    {
        $jade = new Jade();
        $jade->share('answear', 42);
        $jade->share(array(
            'foo' => 'Hello',
            'bar' => 'world',
        ));
        $html = $jade->render("p=foo\ndiv=answear");
        $this->assertSame('<p>Hello</p><div>42</div>', $html);

        $html = $jade->render("p=foo\ndiv=answear", array(
            'answear' => 16,
        ));
        $this->assertSame('<p>Hello</p><div>16</div>', $html);

        $html = $jade->render("p\n  =foo\n  =' '\n  =bar\n  | !");
        $this->assertSame('<p>Hello world!</p>', $html);
    }

    public function testResetSharedVariables()
    {
        $jade = new Jade();
        $jade->share('answear', 42);
        $jade->share(array(
            'foo' => 'Hello',
            'bar' => 'world',
        ));
        $jade->resetSharedVariables();

        $error = null;
        try {
            $jade->render("p\n  =foo\n=' '\n=bar\n  | !");
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertSame('Undefined variable: foo', $error);
    }
}
