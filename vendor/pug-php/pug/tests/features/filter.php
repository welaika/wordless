<?php

use Jade\Jade;

class JadeFilterTest extends PHPUnit_Framework_TestCase
{
    /**
     * custom filter test
     */
    public function testFilter()
    {
        $jade = new Jade();
        $this->assertSame($jade->getFilter('php'), 'Jade\Filter\Php');
        $this->assertFalse($jade->hasFilter('text'));
        $jade->filter('text', function($node, $compiler){
            foreach ($node->block->nodes as $line) {
                $output[] = $compiler->interpolate($line->value);
            }
            return strip_tags(implode(' ', $output));
        });
        $this->assertTrue($jade->hasFilter('text'));
        $actual = $jade->render('
div
    p
        :text
            article <span>foo</span> bar <img title="foo" />
            <div>section</div>
    :text
        <input /> form
        em strong quote code
');
        $expected = '<div><p>article foo bar section</p>form em strong quote code</div>';

        $this->assertSame(str_replace(' ', '', $expected), str_replace(' ', '', $actual), 'Custom filter');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 18
     */
    public function testNonCallableFilter()
    {
        $jade = new Jade();
        $this->assertFalse($jade->hasFilter('bar'));
        $jade->filter('bar', 'nonexists');
        $this->assertTrue($jade->hasFilter('bar'));
        $actual = $jade->render('
div
    p
        :bar
            article <span>foo</span> bar <img title="foo" />
            <div>section</div>
');
    }

    public function testFilterAutoload()
    {
        $jade = new Jade();
        $this->assertFalse($jade->hasFilter('foo-bar'));
        spl_autoload_register(function ($name) {
            $name = explode('\\', $name);
            $file = __DIR__ . '/../lib/' . end($name) . 'Filter.php';
            if (file_exists($file)) {
                include_once $file;
            }
        });
        $jade->setOption('filterAutoLoad', false);
        $this->assertFalse($jade->hasFilter('foo-bar'));
        $this->assertSame($jade->getFilter('foo-bar'), null);
        $jade->setOption('filterAutoLoad', true);
        $this->assertTrue($jade->hasFilter('foo-bar'));
        $this->assertSame($jade->getFilter('foo-bar'), 'Jade\Filter\FooBar');
        $actual = $jade->render('
div
    p
        :foo-bar
            I\'m so small :(
');
        $expected = '<div><p>I\'M SO TALL :)</p></div>';

        $this->assertSame(preg_replace('`\s`', '', $expected), preg_replace('`\s`', '', $actual), 'Autoloaded filter');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 17
     */
    public function testFilterAutoloadWhenClassDoNotExist()
    {
        $jade = new Jade();
        $this->assertFalse($jade->hasFilter('bar-foo'));
        $actual = $jade->render('
div
    p
        :bar-foo
            article <span>foo</span> bar <img title="foo" />
            <div>section</div>
');
    }

    public function testInlineFilter()
    {
        $jade = new Jade();
        $jade->filter('lower', function($node, $compiler){
            foreach ($node->block->nodes as $line) {
                $output[] = $line->value;
            }
            return strtolower(implode(' ', $output));
        });
        $actual = $jade->render('
h1
    | BAR-
    :lower FOO
    | -BAR
');
        $expected = '<h1>BAR-foo-BAR</h1>';

        $this->assertSame($expected, $actual, 'One-line filter');

        $actual = $jade->render('h1 BAR-#[:lower FOO]-BAR');
        $expected = '<h1>BAR-foo-BAR</h1>';

        $this->assertSame($expected, $actual, 'In-line filter');
    }

    /**
     * @group php-filter-prettyprint
     */
    public function testPhpFilterWithoutPrettyprint()
    {
        $jade = new Jade();
        $actual = $jade->render('
h1
    :php
        |BAR-
        echo 6 * 7
        |-BAR
');
        $expected = '<h1>BAR-42-BAR</h1>';

        $this->assertSame($expected, $actual, 'Block filter');

        $actual = $jade->render('
h1
    span BAR-
    :php
        echo 6 * 7
    span -BAR
');
        $expected = '<h1><span>BAR-</span>42<span>-BAR</span></h1>';

        $this->assertSame($expected, $actual, 'Block filter and span');

        $actual = $jade->render('
h1
    | BAR-
    :php echo 6 * 7
    | -BAR
');
        $expected = '<h1>BAR-42-BAR</h1>';

        $this->assertSame($expected, $actual, 'One-line filter');

        $actual = $jade->render('h1 BAR-#[:php echo 6 * 7]-BAR');
        $expected = '<h1>BAR-42-BAR</h1>';

        $this->assertSame($expected, $actual, 'In-line filter');
    }

    /**
     * @group php-filter-prettyprint
     */
    public function testPhpFilterWithPrettyprint()
    {
        $jade = new Jade(array(
            'prettyprint' => true,
        ));
        $actual = trim($jade->render('
h1
    :php
        | BAR-
        echo 6 * 7
        | -BAR
'));
        $expected = '/^<h1>\n    BAR-42\s+-BAR\s*\n<\/h1>$/';

        $this->assertRegExp($expected, $actual, 'Block filter');

        $actual = trim($jade->render('
h1
    span BAR-
    :php
        echo 6 * 7
    span -BAR
'));
        $expected = '/^<h1>\s+<span>BAR-<\/span>\s+42\s+<span>-BAR<\/span><\/h1>$/';

        $this->assertRegExp($expected, $actual, 'Block filter and span');

        $actual = trim($jade->render('
h1
    | BAR-
    :php echo 6 * 7
    | -BAR
'));
        $expected = '/^<h1>\s+BAR-\s+42\s+-BAR\s*<\/h1>$/';

        $this->assertRegExp($expected, $actual, 'One-line filter');

        $actual = $jade->render('h1 BAR-#[:php echo 6 * 7]-BAR');
        $expected = '/^<h1>\s+BAR-\s+42\s+-BAR\s*<\/h1>$/';

        $this->assertRegExp($expected, $actual, 'In-line filter');
    }
}
