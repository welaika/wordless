<?php

use Jade\Jade;

class JadeSettingsTest extends PHPUnit_Framework_TestCase
{
    static private function rawHtml($html, $convertSingleQuote = true)
    {
        $html = str_replace(array("\r", ' '), '', $html);
        if ($convertSingleQuote) {
            $html = strtr($html, "'", '"');
        }
        return trim(preg_replace('`\n{2,}`', "\n", $html));
    }

    static private function simpleHtml($html)
    {
        return trim(preg_replace('`\r\n|\r|\n\s*\n`', "\n", $html));
    }

    /**
     * keepNullAttributes setting test
     */
    public function testKeepNullAttributes()
    {
        $jade = new Jade(array(
            'singleQuote' => false,
            'keepNullAttributes' => false,
            'prettyprint' => true,
        ));
        $templates = dirname(__FILE__) . '/../templates/';
        $actual = $jade->render(file_get_contents($templates . 'mixin.attrs.jade'));
        $expected = file_get_contents($templates . 'mixin.attrs.html');

        $this->assertSame(static::rawHtml($actual), static::rawHtml($expected), 'Keep null attributes disabled');

        $jade = new Jade(array(
            'singleQuote' => false,
            'keepNullAttributes' => true,
            'prettyprint' => true,
        ));
        $templates = dirname(__FILE__) . '/../templates/';
        $actual = $jade->render(file_get_contents($templates . 'mixin.attrs.jade'));
        $expected = file_get_contents($templates . 'mixin.attrs-keep-null-attributes.html');

        $this->assertSame(static::rawHtml($actual), static::rawHtml($expected), 'Keep null attributes enabled');
    }

    /**
     * prettyprint setting test
     */
    public function testPrettyprint()
    {
        $template = '
mixin centered(title)
  div.centered(id=attributes.id)
    - if (title)
      h1(class=attributes.class)= title
    block
    - if (attributes.href)
      .footer
        a(href=attributes.href) Back

+centered(\'Section 1\')#Second.foo
  p Some important content.
';

        $jade = new Jade(array(
            'singleQuote' => true,
            'prettyprint' => true,
        ));
        $actual = trim(preg_replace('`\n[\s\n]+`', "\n", str_replace("\r", '', preg_replace('`[ \t]+`', ' ', $jade->render($template)))));
        $expected = str_replace("\r", '', '<div id=\'Second\' class=\'centered\'>
<h1 class=\'foo\'>Section 1</h1>
<p>Some important content.</p>
</div>');

        $this->assertSame($expected, $actual, 'Pretty print enabled');

        $jade = new Jade(array(
            'singleQuote' => true,
            'prettyprint' => false,
        ));
        $actual = preg_replace('`[ \t]+`', ' ', $jade->render($template));
        $expected =  '<div id=\'Second\' class=\'centered\'><h1 class=\'foo\'>Section 1</h1><p>Some important content.</p></div>';

        $this->assertSame($expected, $actual, 'Pretty print disabled');
    }

    /**
     * setOption test
     */
    public function testSetOption()
    {
        $template = '
mixin centered(title)
  div.centered(id=attributes.id)
    - if (title)
      h1(class=attributes.class)= title
    block
    - if (attributes.href)
      .footer
        a(href=attributes.href) Back

+centered(\'Section 1\')#Second.foo
  p Some important content.
';

        $jade = new Jade(array(
            'singleQuote' => true,
            'prettyprint' => false,
        ));
        $this->assertFalse($jade->getOption('prettyprint'), 'getOption should return current setting');
        $jade->setOption('prettyprint', true);
        $this->assertTrue($jade->getOption('prettyprint'), 'getOption should return current setting');
        $actual = trim(preg_replace('`\n[\s\n]+`', "\n", str_replace("\r", '', preg_replace('`[ \t]+`', ' ', $jade->render($template)))));
        $expected = str_replace("\r", '', '<div id=\'Second\' class=\'centered\'>
<h1 class=\'foo\'>Section 1</h1>
<p>Some important content.</p>
</div>');

        $this->assertSame($actual, $expected, 'Pretty print enabled');

        $jade->setOption('prettyprint', false);
        $this->assertFalse($jade->getOption('prettyprint'), 'getOption should return current setting');
        $actual = preg_replace('`[ \t]+`', ' ', $jade->render($template));
        $expected =  '<div id=\'Second\' class=\'centered\'><h1 class=\'foo\'>Section 1</h1><p>Some important content.</p></div>';

        $this->assertSame($actual, $expected, 'Pretty print disabled');
    }

    /**
     * setOptions test
     */
    public function testSetOptions()
    {
        $jade = new Jade();
        $jade->setOptions(array(
            'prettyprint' => true,
            'cache' => 'abc',
            'indentChar' => '-',
        ));
        $this->assertTrue($jade->getOption('prettyprint'));
        $this->assertSame($jade->getOption('cache'), 'abc');
        $this->assertSame($jade->getOption('indentChar'), '-');
    }

    /**
     * setCustomOption test
     */
    public function testSetCustomOption()
    {
        $jade = new Jade();
        $jade->setCustomOption('i-do-not-exists', 'right');
        $this->assertSame($jade->getOption('i-do-not-exists'), 'right', 'getOption should return custom setting');
    }

    /**
     * setOptions test
     */
    public function testSetCustomOptions()
    {
        $jade = new Jade();
        $jade->setCustomOptions(array(
            'prettyprint' => false,
            'foo' => 'bar',
        ));
        $this->assertFalse($jade->getOption('prettyprint'));
        $this->assertSame($jade->getOption('foo'), 'bar');
    }

    /**
     * allowMixinOverride setting test
     */
    public function testAllowMixinOverride()
    {
        $template = '
mixin foo()
  h1 Hello

mixin foo()
  h2 Hello

+foo
';

        $jade = new Jade(array(
            'singleQuote' => false,
            'allowMixinOverride' => true,
        ));
        $actual = $jade->render($template);
        $expected = '<h2>Hello</h2>';

        $this->assertSame(static::rawHtml($actual), static::rawHtml($expected), 'Allow mixin override enabled');

        $jade = new Jade(array(
            'singleQuote' => false,
            'allowMixinOverride' => false,
        ));
        $actual = $jade->render($template);
        $expected = '<h1>Hello</h1>';

        $this->assertSame(static::rawHtml($actual), static::rawHtml($expected), 'Allow mixin override disabled');
    }

    /**
     * allowMixinOverride setting test
     */
    public function testRestrictedScope()
    {
        $template = '
mixin foo()
  if isset($bar)
    h1=bar
  else
    h1 Not found
  block

- bar="Hello"

+foo
  if isset($bar)
    h2=bar
  else
    h2 Not found
';

        $jade = new Jade(array(
            'restrictedScope' => true,
        ));
        $actual = $jade->render($template);
        $expected = '<h1>Not found</h1><h2>Not found</h2>';

        $this->assertSame(static::rawHtml($actual), static::rawHtml($expected), 'Restricted scope enabled');

        $jade->setOption('restrictedScope', false);
        $actual = $jade->render($template);
        $expected = '<h1>Hello</h1><h2>Hello</h2>';

        $this->assertSame(static::rawHtml($actual), static::rawHtml($expected), 'Restricted scope disabled');
    }

    /**
     * singleQuote setting test
     */
    public function testSingleQuote()
    {
        $template = 'h1#foo.bar(style="color: red;") Hello';

        $jade = new Jade(array(
            'prettyprint' => true,
            'singleQuote' => true,
        ));
        $actual = $jade->render($template);
        $expected = "<h1 id='foo' style='color: red;' class='bar'>Hello</h1>";

        $this->assertSame(static::rawHtml($actual, false), static::rawHtml($expected, false), 'Single quote enabled on a simple header');
        $file = __DIR__ . '/../templates/attrs-data.complex';
        $this->assertSame(static::simpleHtml($jade->render($file . '.jade')), static::simpleHtml(file_get_contents($file . '.single-quote.html')), 'Single quote enabled on attrs-data.complex');
        $file = __DIR__ . '/../templates/attrs-data';
        $this->assertSame(static::simpleHtml($jade->render($file . '.jade')), static::simpleHtml(file_get_contents($file . '.single-quote.html')), 'Single quote enabled on attrs-data');
        $file = __DIR__ . '/../templates/object-to-css';
        $this->assertSame(static::simpleHtml($jade->render($file . '.jade')), static::simpleHtml(file_get_contents($file . '.single-quote.html')), 'Single quote enabled on object-to-css');
        $file = __DIR__ . '/../templates/interpolation';
        $this->assertSame(static::simpleHtml($jade->render($file . '.jade')), static::simpleHtml(file_get_contents($file . '.single-quote.html')), 'Single quote enabled on interpolation');

        $jade = new Jade(array(
            'prettyprint' => true,
            'singleQuote' => false,
        ));
        $actual = $jade->render($template);
        $expected = '<h1 id="foo" style="color: red;" class="bar">Hello</h1>';

        $this->assertSame(static::rawHtml($actual, false), static::rawHtml($expected, false), 'Single quote disabled on a simple header');
        $file = __DIR__ . '/../templates/attrs-data.complex';
        $this->assertSame(static::simpleHtml($jade->render($file . '.jade')), static::simpleHtml(file_get_contents($file . '.html')), 'Single quote disabled on attrs-data.complex');
        $file = __DIR__ . '/../templates/attrs-data';
        $this->assertSame(static::simpleHtml($jade->render($file . '.jade')), static::simpleHtml(file_get_contents($file . '.html')), 'Single quote disabled on attrs-data');
        $file = __DIR__ . '/../templates/object-to-css';
        $this->assertSame(static::simpleHtml($jade->render($file . '.jade')), static::simpleHtml(file_get_contents($file . '.html')), 'Single quote disabled on object-to-css');
        $file = __DIR__ . '/../templates/interpolation';
        $this->assertSame(static::simpleHtml($jade->render($file . '.jade')), static::simpleHtml(file_get_contents($file . '.html')), 'Single quote disabled on interpolation');
    }

    /**
     * phpSingleLine setting test
     */
    public function testPhpSingleLine()
    {
        $template = '
- $foo = "bar"
- $bar = 42
p(class=$foo)=$bar
';

        $jade = new Jade(array(
            'phpSingleLine' => true,
        ));
        $compile = $jade->compile($template);
        $actual = substr_count($compile, "\n");
        $expected = substr_count($compile, '<?php') * 2 + 1;

        $this->assertSame($expected, $actual, 'PHP single line enabled');
        $this->assertGreaterThan(5, $actual, 'PHP single line enabled');

        $jade = new Jade(array(
            'phpSingleLine' => false,
        ));
        $actual = substr_count(trim($jade->compile($template)), "\n");

        $this->assertLessThan(2, $actual,'PHP single line disabled');
    }

    /**
     * Return HTML if mixed indent is allowed
     */
    public function testAllowMixedIndentEnabled()
    {
        $jade = new Jade(array(
            'allowMixedIndent' => true,
        ));
        $actual = $jade->render('p' . "\n\t    " . 'i Hi' . "\n    \t" . 'i Ho');
        $expected = '<p><i>Hi</i><i>Ho</i></p>';

        $this->assertSame(static::rawHtml($actual, false), static::rawHtml($expected, false), 'Allow mixed indent enabled');

        $actual = $jade->render('p' . "\n    \t" . 'i Hi' . "\n\t    " . 'i Ho');
        $expected = '<p><i>Hi</i><i>Ho</i></p>';

        $this->assertSame(static::rawHtml($actual, false), static::rawHtml($expected, false), 'Allow mixed indent enabled');
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 20
     */
    public function testAllowMixedIndentDisabledTabSpaces()
    {
        $jade = new Jade(array(
            'allowMixedIndent' => false,
        ));

        $jade->render('p' . "\n\t    " . 'i Hi');
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 20
     */
    public function testAllowMixedIndentDisabledSpacesTab()
    {
        $jade = new Jade(array(
            'allowMixedIndent' => false,
        ));

        $jade->render('p' . "\n    \t" . 'i Hi');
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 20
     */
    public function testAllowMixedIndentDisabledSpacesTabAfterSpaces()
    {
        $jade = new Jade(array(
            'allowMixedIndent' => false,
        ));

        $jade->render('p' . "\n        " . 'i Hi' . "\n    \t" . 'i Hi');
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 25
     */
    public function testAllowMixedIndentDisabledSpacesAfterTab()
    {
        $jade = new Jade(array(
            'allowMixedIndent' => false,
        ));

        $jade->render('p' . "\n\t" . 'i Hi' . "\n    " . 'i Hi');
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 25
     */
    public function testAllowMixedIndentDisabledSpacesTextAfterTab()
    {
        $jade = new Jade(array(
            'allowMixedIndent' => false,
        ));

        $jade->render('p' . "\n\t" . 'i Hi' . "\np.\n    " . 'Hi');
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 25
     */
    public function testAllowMixedIndentDisabledSpacesTabTextAfterTab()
    {
        $jade = new Jade(array(
            'allowMixedIndent' => false,
        ));

        $jade->render('p' . "\n\t\t" . 'i Hi' . "\np\n    \t" . 'i Hi');
    }

    /**
     * Static includeNotFound is deprecated, use the notFound option instead.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 22
     */
    public function testIncludeNotFoundDisabledViaStaticVariable()
    {
        $save = \Jade\Parser::$includeNotFound;
        $jade = new Jade();
        \Jade\Parser::$includeNotFound = false;

        $error = null;

        try {
            $jade->render('include does-not-exists');
        } catch (\Exception $e) {
            $error = $e;
        }

        \Jade\Parser::$includeNotFound = $save;

        if ($error) {
            throw $error;
        }
    }

    /**
     * notFound option replace the static variable includeNotFound.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 22
     * @expectedExceptionMessageRegExp /does-not-exists/
     */
    public function testIncludeNotFoundDisabledViaOption()
    {
        $jade = new Jade(array(
            'notFound' => false
        ));
        $jade->render('include does-not-exists');
    }

    /**
     * includeNotFound return an error included in content if a file miss.
     */
    public function testIncludeNotFoundEnabledViaStatic()
    {
        $jade = new Jade();
        $this->assertTrue(!empty(\Jade\Parser::$includeNotFound), 'includeNotFound should be set by default.');

        $actual = $jade->render('include does-not-exists');
        $notFound = $jade->render(\Jade\Parser::$includeNotFound);
        $this->assertSame($actual, $notFound, 'A file not found when included should return default includeNotFound value if touched.');

        $save = \Jade\Parser::$includeNotFound;
        \Jade\Parser::$includeNotFound = 'h1 Hello';
        $actual = $jade->render('include does-not-exists');
        $this->assertSame($actual, '<h1>Hello</h1>', 'A file not found when included should return includeNotFound value if set.');
        \Jade\Parser::$includeNotFound = $save;
    }

    /**
     * notFound option return an error included in content if a file miss.
     */
    public function testIncludeNotFoundEnabledViaOption()
    {
        $jade = new Jade();
        $actual = $jade->render('include does-not-exists');
        $notFound = $jade->render(\Jade\Parser::$includeNotFound);
        $this->assertSame($actual, $notFound, 'A file not found when included should return default includeNotFound value if the notFound option is not set.');

        $jade = new Jade(array(
            'notFound' => 'p My Not Found Error'
        ));
        $actual = $jade->render('include does-not-exists');
        $this->assertSame($actual, '<p>My Not Found Error</p>', 'A file not found when included should return notFound value if set.');
    }

    /**
     * indentChar and indentSize allow to configure the indentation.
     */
    public function testIndent()
    {
        $template = '
body
  header
    h1#foo Hello!
  section
    article
      p Bye!';

        $jade = new Jade(array(
            'singleQuote' => false,
            'prettyprint' => true,
            'indentSize' => 2,
            'indentChar' => ' ',
        ));
        $actual = str_replace("\r", '', $jade->render($template));
        $expected = str_replace("\r", '', '<body>
  <header>
    <h1 id="foo">Hello!</h1>
  </header>
  <section>
    <article>
      <p>Bye!</p>
    </article>
  </section>
</body>
');
        $this->assertSame($expected, $actual);

        $jade = new Jade(array(
            'singleQuote' => false,
            'prettyprint' => true,
            'indentSize' => 4,
            'indentChar' => ' ',
        ));
        $actual = str_replace("\r", '', $jade->render($template));
        $expected = str_replace('  ', '    ', $expected);
        $this->assertSame($expected, $actual);

        $jade = new Jade(array(
            'singleQuote' => false,
            'prettyprint' => true,
            'indentSize' => 1,
            'indentChar' => "\t",
        ));
        $actual = str_replace("\r", '', $jade->render($template));
        $expected = str_replace('    ', "\t", $expected);
        $this->assertSame($expected, $actual);
    }

    /**
     * notFound option replace the static variable includeNotFound.
     *
     * @expectedException \ErrorException
     * @expectedExceptionCode 29
     */
    public function testNoBaseDir()
    {
        $jade = new Jade();
        $jade->render(__DIR__ . '/../templates/auxiliary/include-sibling.jade');
    }

    public function renderWithBaseDir($basedir, $template)
    {
        $jade = new Jade(array(
            'prettyprint' => true,
            'basedir' => $basedir,
        ));
        $code = $jade->render($template);

        return trim(preg_replace('/\n\s+/', "\n", str_replace("\r", '', $code)));
    }

    public function testBaseDir()
    {
        $actual = $this->renderWithBaseDir(
            __DIR__ . '/..',
            __DIR__ . '/../templates/auxiliary/include-sibling.jade'
        );
        $expected = "<p>World</p>\n" .
            "<div class=\"alert alert-danger\">Page not found.</div>\n".
            "<div class=\"alert alert-danger\">Page not found.</div>\n" .
            "<p>World</p>\n" .
            "<div class=\"alert alert-danger\">Page not found.</div>\n" .
            "<div class=\"alert alert-danger\">Page not found.</div>";
        $this->assertSame($expected, $actual);

        $actual = $this->renderWithBaseDir(
            __DIR__ . '/../templates/',
            __DIR__ . '/../templates/auxiliary/include-sibling.jade'
        );
        $expected = "<p>World</p>\n" .
            "<div class=\"alert alert-danger\">Page not found.</div>\n".
            "<p>World</p>\n" .
            "<p>World</p>\n" .
            "<div class=\"alert alert-danger\">Page not found.</div>\n" .
            "<p>World</p>";
        $this->assertSame($expected, $actual);

        $actual = $this->renderWithBaseDir(
            __DIR__ . '/../templates/auxiliary',
            __DIR__ . '/../templates/auxiliary/include-sibling.jade'
        );
        $expected = "<p>World</p>\n" .
            "<p>World</p>\n".
            "<div class=\"alert alert-danger\">Page not found.</div>\n" .
            "<p>World</p>\n" .
            "<p>World</p>\n" .
            "<div class=\"alert alert-danger\">Page not found.</div>";
        $this->assertSame($expected, $actual);

        $actual = $this->renderWithBaseDir(
            __DIR__ . '/../templates/auxiliary/nothing',
            __DIR__ . '/../templates/auxiliary/include-sibling.jade'
        );
        $expected = "<p>World</p>\n" .
            "<div class=\"alert alert-danger\">Page not found.</div>\n".
            "<div class=\"alert alert-danger\">Page not found.</div>\n" .
            "<p>World</p>\n" .
            "<div class=\"alert alert-danger\">Page not found.</div>\n" .
            "<div class=\"alert alert-danger\">Page not found.</div>";
        $this->assertSame($expected, $actual);

        $actual = $this->renderWithBaseDir(
            __DIR__ . '/../templates/',
            __DIR__ . '/../templates/auxiliary/include-basedir.jade'
        );
        $expected = "<p>World</p>\n" .
            "<div class=\"alert alert-danger\">Page not found.</div>\n" .
            "<p>World</p>\n" .
            "<p>World</p>\n" .
            "<div class=\"alert alert-danger\">Page not found.</div>\n" .
            "<p>World</p>";
        $this->assertSame($expected, $actual);

        $actual = $this->renderWithBaseDir(
            __DIR__ . '/../templates/',
            __DIR__ . '/../templates/auxiliary/extends-basedir.jade'
        );
        $expected =
            "<html>\n" .
            "<head>\n" .
            "<title>My Application</title>\n" .
            "</head>\n" .
            "<body>\n" .
            "<h1>test</h1>\n" .
            "<p>World</p>\n" .
            "<div class=\"alert alert-danger\">Page not found.</div>\n" .
            "<p>World</p>\n" .
            "<p>World</p>\n" .
            "<div class=\"alert alert-danger\">Page not found.</div>\n" .
            "<p>World</p>\n" .
            "</body>\n" .
            "</html>";
        $this->assertSame($expected, $actual);
    }

    public function testClassAttribute()
    {
        $jade = new Jade(array(
            'singleQuote' => false,
            'classAttribute' => 'className',
        ));
        $actual = trim($jade->render('.foo.bar Hello'));
        $expected = '<div className="foo bar">Hello</div>';
        $this->assertSame($expected, $actual);
    }
}
