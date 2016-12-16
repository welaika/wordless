<?php

use Pug\Pug;

class JadeHooksTest extends PHPUnit_Framework_TestCase
{
    public function testPreRender()
    {
        $pug = new Pug(array(
            'preRender' => function ($pugCode) {
                return preg_replace('/\*\*btn/', 'class="btn btn-primary" data-button="on"', $pugCode);
            },
        ));
        $html = $pug->render('a#foo.bar(**btn title="Foo") Hello');
        $expected = '<a id="foo" data-button="on" title="Foo" class="bar btn btn-primary">Hello</a>';

        $this->assertSame($expected, $html);
    }

    public function testPreRenderIncludeAndExtend()
    {
        $pug = new Pug(array(
            'basedir' => __DIR__ . '/../templates/auxiliary',
            'preRender' => function ($pugCode) {
                return str_replace(
                    array('My Application', 'case 42'),
                    array('Foobar', 'case 1138'),
                    $pugCode
                );
            },
        ));
        $html = preg_replace('/\s/', '',$pug->render(
            'extends /layout' . "\n" .
            'block content' . "\n" .
            '  include /world'
        ));
        $expected = preg_replace('/\s/', '',
            '<html>' .
            '  <head>' .
            '    <title>Foobar</title>' .
            '  </head>' .
            '  <body>' .
            '    <p>THX</p>' .
            '  </body>' .
            '</html>'
        );

        $this->assertSame($expected, $html);
    }

    public function testPostRender()
    {
        $pug = new Pug(array(
            'postRender' => function ($phpCode) {
                return str_replace('?>>', '?> data-dynamic="true">', $phpCode);
            },
        ));
        $html = $pug->render('a#foo(title=5*3) Hello');
        $expected = '<a id="foo" title="15" data-dynamic="true">Hello</a>';

        $this->assertSame($expected, $html);
    }
}
