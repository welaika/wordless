<?php

use JsPhpize\JsPhpize;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group disallow
     */
    public function testDisallow()
    {
        $jsPhpize = new JsPhpize(array(
            'disallow' => 'foo bar',
        ));
        $this->assertSame(6, $jsPhpize->render('
            var a = 3;
            for (i = 0; i < 3; i++) {
                a++; // increment
            }
            return a;
        '));


        $jsPhpize = new JsPhpize(array(
            'disallow' => 'foo comment bar',
        ));
        $code = null;
        try {
            $jsPhpize->render('
                var a = 3;
                for (i = 0; i < 3; i++) {
                    a++; // increment
                }
                return a;
            ');
        } catch (\Exception $e) {
            $code = $e->getCode();
        }
        $this->assertSame(3, $code);
    }

    /**
     * @expectedException     \JsPhpize\Lexer\Exception
     * @expectedExceptionCode 1
     */
    public function testConstPrefixRestriction()
    {
        $jsPhpize = new JsPhpize(array(
            'constPrefix' => 'FOO',
        ));
        $jsPhpize->render('
            var a = FOOBAR;
        ');
    }

    /**
     * @expectedException     \JsPhpize\Lexer\Exception
     * @expectedExceptionCode 4
     */
    public function testVarPrefixRestriction()
    {
        $jsPhpize = new JsPhpize(array(
            'varPrefix' => 'test',
        ));
        $jsPhpize->render('
            var a = test_zz;
        ');
    }
}
