<?php

use JsPhpize\JsPhpize;

class MainMethodsTest extends \PHPUnit_Framework_TestCase
{
    public function testCompileFile()
    {
        $jsPhpize = new JsPhpize();
        $actual = $jsPhpize->compileFile(__DIR__ . '/../examples/basic.js');
        $expected = <<<'EOD'
$GLOBALS['__jpv_dot'] = function ($base) {
    foreach (array_slice(func_get_args(), 1) as $key) {
        $base = is_array($base)
            ? (isset($base[$key]) ? $base[$key] : null)
            : (is_object($base)
                ? (isset($base->$key)
                    ? $base->$key
                    : (method_exists($base, $method = "get" . ucfirst($key))
                        ? $base->$method()
                        : (method_exists($base, $key)
                            ? array($base, $key)
                            : null
                        )
                    )
                )
                : null
            );
    }

    return $base;
};
$GLOBALS['__jpv_plus'] = function ($base) {
    foreach (array_slice(func_get_args(), 1) as $value) {
        $base = is_string($base) || is_string($value) ? $base . $value : $base + $value;
    }

    return $base;
};
$foo = array( 'bar' => array( "baz" => "hello" ) );
$biz = 'bar';
return call_user_func($GLOBALS['__jpv_plus'], call_user_func($GLOBALS['__jpv_dot'], $foo, 'bar', "baz"), ' ', call_user_func($GLOBALS['__jpv_dot'], $foo, $biz, 'baz'), " ", call_user_func($GLOBALS['__jpv_dot'], $foo, 'bar', 'baz'));
EOD;
        $actual = str_replace(';', ";\n", preg_replace('/\s/', '', $actual));
        $expected = str_replace(';', ";\n", preg_replace('/\s/', '', $expected));
        $this->assertSame($expected, $actual);
        $this->assertSame('', $jsPhpize->compileDependencies());

        $actual = $jsPhpize->compileFile(__DIR__ . '/../examples/basic.js', true);
        $expected = <<<'EOD'
$foo = array( 'bar' => array( "baz" => "hello" ) );
$biz = 'bar';
return call_user_func($GLOBALS['__jpv_plus'], call_user_func($GLOBALS['__jpv_dot'], $foo, 'bar', "baz"), ' ', call_user_func($GLOBALS['__jpv_dot'], $foo, $biz, 'baz'), " ", call_user_func($GLOBALS['__jpv_dot'], $foo, 'bar', 'baz'));
EOD;
        $actual = preg_replace('/\s/', '', $actual);
        $expected = preg_replace('/\s/', '', $expected);
        $this->assertSame($expected, $actual);

        $actual = $jsPhpize->compileDependencies();
        $expected = <<<'EOD'
$GLOBALS['__jpv_dot'] = function ($base) {
    foreach (array_slice(func_get_args(), 1) as $key) {
        $base = is_array($base)
            ? (isset($base[$key]) ? $base[$key] : null)
            : (is_object($base)
                ? (isset($base->$key)
                    ? $base->$key
                    : (method_exists($base, $method = "get" . ucfirst($key))
                        ? $base->$method()
                        : (method_exists($base, $key)
                            ? array($base, $key)
                            : null
                        )
                    )
                )
                : null
            );
    }

    return $base;
};
$GLOBALS['__jpv_plus'] = function ($base) {
    foreach (array_slice(func_get_args(), 1) as $value) {
        $base = is_string($base) || is_string($value) ? $base . $value : $base + $value;
    }

    return $base;
};
EOD;
        $actual = preg_replace('/\s/', '', $actual);
        $expected = preg_replace('/\s/', '', $expected);
        $this->assertSame($expected, $actual);

        $jsPhpize->compileFile(__DIR__ . '/../examples/calcul.js', true);
        $actual = $jsPhpize->compileDependencies();
        $expected = <<<'EOD'
$GLOBALS['__jpv_plus'] = function ($base) {
    foreach (array_slice(func_get_args(), 1) as $value) {
        $base = is_string($base) || is_string($value) ? $base . $value : $base + $value;
    }

    return $base;
};
EOD;
        $actual = preg_replace('/\s/', '', $actual);
        $expected = preg_replace('/\s/', '', $expected);
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException              \Exception
     * @expectedExceptionMessageRegExp /No such file/
     */
    public function testCompileFileMissing()
    {
        try {
            $jsPhpize = new JsPhpize();
            $jsPhpize->compileFile('does/not/exists.js');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 1);
        }
    }

    public function testCompileConcat()
    {
        $jsPhpize = new JsPhpize();
        $actual = $jsPhpize->render('return "group[" + group.id + "]"', array(
            'group' => (object) array(
                'id' => 4,
            ),
        ));
        $expected = 'group[4]';

        $this->assertSame($expected, $actual);
    }

    /**
     * @group concat
     */
    public function testConcatenation()
    {
        $jsPhpize = new JsPhpize();
        $actual = $jsPhpize->render("return 'a' + a.i", array(
            'a' => array(
                'i' => 'b',
            ),
        ));
        $expected = 'ab';

        $this->assertSame($expected, $actual);
    }

    public function testCompileSource()
    {
        $jsPhpize = new JsPhpize(array(
            'varPrefix' => 'foo',
        ));
        $actual = $jsPhpize->compileCode('b = 8');
        $expected = '$b = 8;';
        $actual = preg_replace('/\s/', '', $actual);
        $expected = preg_replace('/\s/', '', $expected);
        $this->assertSame($expected, $actual);

        $dir = getcwd();
        chdir(__DIR__ . '/../examples');
        $actual = $jsPhpize->compileCode('calcul.js');
        chdir($dir);
        $expected = <<<'EOD'
$GLOBALS['foodot'] = function ($base) {
    foreach (array_slice(func_get_args(), 1) as $key) {
        $base = is_array($base)
            ? (isset($base[$key]) ? $base[$key] : null)
            : (is_object($base)
                ? (isset($base->$key)
                    ? $base->$key
                    : (method_exists($base, $method = "get" . ucfirst($key))
                        ? $base->$method()
                        : (method_exists($base, $key)
                            ? array($base, $key)
                            : null
                        )
                    )
                )
                : null
            );
    }

    return $base;
};
call_user_func($GLOBALS['foodot'], $calcul, 'js');
EOD;
        $actual = preg_replace('/\s/', '', $actual);
        $expected = preg_replace('/\s/', '', $expected);
        $this->assertSame($expected, $actual);
    }

    public function testRender()
    {
        $jsPhpize = new JsPhpize();
        $actual = $jsPhpize->render('return b;', array(
            'b' => 42,
        ));
        $expected = 42;
        $this->assertSame($expected, $actual);

        error_reporting(E_ALL ^ E_NOTICE);
        $actual = $jsPhpize->render('return b;');
        $expected = null;
        $this->assertSame($expected, $actual);

        $jsPhpize->share('b', array(31));
        $actual = $jsPhpize->render('return b;');
        $expected = array(31);
        $this->assertSame($expected, $actual);

        $jsPhpize->resetSharedVariables();
        $actual = $jsPhpize->render('return b;');
        $expected = null;
        $this->assertSame($expected, $actual);
        error_reporting(-1);
    }
}
