<?php

use Pug\Pug;

class JadeIssuesTest extends PHPUnit_Framework_TestCase
{
    public function testIssue62()
    {
        $pug = new Pug();
        $html = trim($pug->render('.MyInitialClass(class=$classes)', array(
            'classes' => 'MyClass',
        )));
        $expected = '<div class="MyInitialClass MyClass"></div>';

        $this->assertSame($expected, $html);
    }

    public function testIssue64()
    {
        $pug = new Pug();
        $html = trim($pug->render("script.\n" . '  var url = "/path/#{$foo->bar}/file";', array(
            'foo' => (object) array(
                'bar' => 'hello/world',
            ),
        )));
        $expected = '<script>var url = "/path/hello/world/file";</script>';

        $this->assertSame($expected, $html);
    }

    public function testIssue71()
    {
        $pug = new Pug(array(
            'singleQuote' => false,
            'expressionLanguage' => 'js',
        ));
        $actual = trim($pug->render('input(type="checkbox", name="group[" + group.id + "]")', array(
            'group' => (object) array(
                'id' => 4,
            ),
        )));

        $this->assertSame('<input type="checkbox" name="group[4]">', $actual);
    }

    public function testIssue73()
    {
        $pug = new Pug();
        $actual = trim($pug->render('p=__("foo")'));

        $this->assertSame('<p>foo</p>', $actual);
    }

    public function testIssue75()
    {
        $pug = new Pug(array(
            'cache' => false,
        ));
        $requirements = $pug->requirements();

        $this->assertTrue($requirements['cacheFolderExists']);
        $this->assertTrue($requirements['cacheFolderIsWritable']);
    }

    public function testIssue86()
    {
        $pug = new Pug(array(
            'expressionLanguage' => 'js',
        ));
        $actual = trim($pug->render("a(href='?m=' + i.a)=i.b", array(
            'i' => array(
                'a' => 1,
                'b' => 2,
            ),
        )));
        $expected = '<a href="?m=1">2</a>';

        $this->assertSame($expected, $actual);
    }
}
