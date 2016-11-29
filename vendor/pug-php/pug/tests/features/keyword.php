<?php

use Jade\Jade;

class ForKeyword
{
    public function __invoke($args)
    {
        return $args;
    }
}

class BadOptionType
{
}

class JadeKeywordTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 30
     */
    public function testInvalidAction()
    {
        $jade = new Jade();
        $jade->addKeyword('foo', 'bar');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 31
     */
    public function testAddAlreadySetKeyword()
    {
        $jade = new Jade();
        $jade->addKeyword('foo', function () {
            return array();
        });
        $jade->addKeyword('foo', function () {
            return 'foo';
        });
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 32
     */
    public function testReplaceNonSetKeyword()
    {
        $jade = new Jade();
        $jade->replaceKeyword('foo', function () {
            return array();
        });
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 34
     */
    public function testBadReturn()
    {
        $jade = new Jade();
        $jade->addKeyword('foo', function () {
            return 32;
        });
        $jade->render('foo');
    }

    public function testBadReturnPreviousException()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Not compatible with HHVM');
        }

        try {
            $jade = new Jade();
            $jade->addKeyword('foo', function () {
                return 32;
            });
            $jade->render('foo');
        } catch (\Exception $e) {
            $code = $e->getPrevious()->getCode();
        }

        $this->assertSame(33, $code, 'Expected previous exception code should be 8 for BadReturn.');
    }

    public function testBadCustomKeywordOptionType()
    {
        $jade = new Jade();
        $jade->setOption('customKeywords', new BadOptionType());
        $jade->addKeyword('foo', function () {
            return 'foo';
        });
        $this->assertSame('foo', $jade->render('foo'));
    }

    public function testPhpKeyWord()
    {
        $jade = new Jade(array(
            'prettyprint' => false,
        ));

        $actual = trim($jade->render('for ;;'));
        $expected = '<for>;;</for>';
        $this->assertSame($expected, $actual, 'Before adding keyword, a word render as a tag.');

        $jade->addKeyword('for', function ($args) {
            return array(
                'beginPhp' => 'for (' . $args . ') {',
                'endPhp' => '}',
            );
        });
        $actual = trim($jade->render(
            'for $i = 0; $i < 3; $i++' . "\n" .
            '  p= i'
        ));
        $expected = '<p>0</p><p>1</p><p>2</p>';
        $this->assertSame($expected, $actual, 'addKeyword should allow to customize available keywords.');
        $jade->replaceKeyword('for', new ForKeyword());
        $actual = trim($jade->render(
            'for $i = 0; $i < 3; $i++' . "\n" .
            '  p'
        ));
        $expected = '$i = 0; $i < 3; $i++<p></p>';
        $this->assertSame($expected, $actual, 'The keyword action can be an callable class.');

        $jade->removeKeyword('for');
        $actual = trim($jade->render('for ;;'));
        $expected = '<for>;;</for>';
        $this->assertSame($expected, $actual, 'After removing keyword, a word render as a tag.');
    }

    public function testHtmlKeyWord()
    {
        $jade = new Jade(array(
            'singleQuote' => false,
            'prettyprint' => false,
        ));

        $actual = trim($jade->render(
            "user Bob\n" .
            '  img(src="bob.png")'
        ));
        $expected = '<user>Bob<img src="bob.png"></user>';
        $this->assertSame($expected, $actual, 'Before adding keyword, a word render as a tag.');

        $jade->addKeyword('user', function ($args) {
            return array(
                'begin' => '<div class="user" title="' . $args . '">',
                'end' => '</div>',
            );
        });
        $actual = trim($jade->render(
            "user Bob\n" .
            '  img(src="bob.png")'
        ));
        $expected = '<div class="user" title="Bob"><img src="bob.png"></div>';
        $this->assertSame($expected, $actual, 'addKeyword should allow to customize available tags.');
    }

    public function testKeyWordBeginAndEnd()
    {
        $jade = new Jade(array(
            'singleQuote' => false,
            'prettyprint' => false,
        ));

        $jade->setKeyword('foo', function ($args) {
            return 'bar';
        });
        $actual = trim($jade->render(
            "foo Bob\n" .
            '  img(src="bob.png")'
        ));
        $expected = 'bar<img src="bob.png">';
        $this->assertSame($expected, $actual, 'If addKeyword return a string, it\'s rendeder before the block.');

        $jade->setKeyword('foo', function ($args) {
            return array(
                'begin' => $args . '/',
            );
        });
        $actual = trim($jade->render(
            "foo Bob\n" .
            '  img(src="bob.png")'
        ));
        $expected = 'Bob/<img src="bob.png">';
        $this->assertSame($expected, $actual, 'If addKeyword return a begin entry, it\'s rendeder before the block.');

        $jade->setKeyword('foo', function ($args) {
            return array(
                'end' => 'bar',
            );
        });
        $actual = trim($jade->render(
            "foo Bob\n" .
            '  img(src="bob.png")'
        ));
        $expected = '<img src="bob.png">bar';
        $this->assertSame($expected, $actual, 'If addKeyword return an end entry, it\'s rendeder after the block.');
    }

    public function testKeyWordArguments()
    {
        $jade = new Jade(array(
            'singleQuote' => false,
            'prettyprint' => false,
        ));

        $foo = function ($args, $block, $keyWord) {
            return $keyWord;
        };
        $jade->setKeyword('foo', $foo);
        $actual = trim($jade->render("foo\n"));
        $expected = 'foo';
        $this->assertSame($expected, $actual);

        $jade->setKeyword('bar', $foo);
        $actual = trim($jade->render("bar\n"));
        $expected = 'bar';
        $this->assertSame($expected, $actual);

        $jade->setKeyword('minify', function ($args, $block) {
            $names = array();
            foreach ($block->nodes as $index => $tag) {
                if ($tag->name === 'link') {
                    $href = $tag->getAttribute('href');
                    $names[] = substr($href['value'], 1, -5);
                    unset($block->nodes[$index]);
                }
            }

            return '<link href="' . implode('-', $names) . '.min.css">';
        });
        $actual = trim($jade->render(
            "minify\n" .
            "  link(href='foo.css')\n" .
            "  link(href='bar.css')\n"
        ));
        $expected = '<link href="foo-bar.min.css">';
        $this->assertSame($expected, $actual);

        $jade->setKeyword('concat-to', function ($args, $block) {
            $names = array();
            foreach ($block->nodes as $index => $tag) {
                if ($tag->name === 'link') {
                    unset($block->nodes[$index]);
                }
            }

            return '<link href="' . $args . '">';
        });
        $actual = trim($jade->render(
            "concat-to app.css\n" .
            "  link(href='foo.css')\n" .
            "  link(href='bar.css')\n"
        ));
        $expected = '<link href="app.css">';
        $this->assertSame($expected, $actual);
    }
}
