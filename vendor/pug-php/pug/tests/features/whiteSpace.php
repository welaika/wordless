<?php

use Jade\Jade;

class JadeWhiteSpaceTest extends PHPUnit_Framework_TestCase
{
    public function testTextarea()
    {
        $jade = new Jade();

        $actual = $jade->render("div\n  textarea");
        $expected = '<div><textarea></textarea></div>';
        $this->assertSame($expected, $actual);

        $actual = $jade->render("div\n  textarea Bob");
        $expected = '<div><textarea>Bob</textarea></div>';
        $this->assertSame($expected, $actual);

        $actual = $jade->render("textarea\n  ='Bob'");
        $expected = '<textarea>Bob</textarea>';
        $this->assertSame($expected, $actual);

        $actual = $jade->render("div\n  textarea.\n    Bob\n    Boby");
        $expected = "<div><textarea>Bob\nBoby</textarea></div>";
        $this->assertSame($expected, $actual);

        $actual = $jade->render("textarea\n  | Bob");
        $expected = '<textarea>Bob</textarea>';
        $this->assertSame($expected, $actual);
    }

    public function testPipeless()
    {
        $jade = new Jade(array(
            'prettyprint' => false,
        ));

        $actual = $jade->render("div\n  span.
            Some indented text
            on many lines
            but the words
            must not
            be
            sticked.
        ");
        $expected = '<div><span>Some indented text on many lines but the words must not be sticked.</span></div>';
        $actual = preg_replace('/\s+/', ' ', $actual);
        $this->assertSame($expected, $actual);
    }
}
