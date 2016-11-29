<?php

use Jade\Parser;
use Jade\Jade;

class EmulateBugException extends \Exception {}
class OnlyOnceException extends \Exception {}

class ExtendParser extends Parser
{
    public function parse()
    {
        static $i = 0;
        if ($i++) {
            throw new OnlyOnceException("E: Works only once", 1);
        }
        parent::parse();
    }
}

class IncludeParser extends Parser
{
    public function parse()
    {
        static $i = 0;
        if ($i++) {
            throw new OnlyOnceException("I: Works only once", 1);
        }
        parent::parse();
    }
}

class JadeExceptionsTest extends PHPUnit_Framework_TestCase
{
    static public function emulateBug()
    {
        throw new EmulateBugException("Error Processing Request", 1);
    }

    /**
     * @expectedException Jade\Parser\Exception
     * @expectedExceptionCode 10
     */
    public function testDoNotUnderstand()
    {
        get_php_code('a(href=="a")');
    }

    /**
     * @expectedException Jade\Parser\Exception
     * @expectedExceptionCode 10
     */
    public function testDoubleDoubleArrow()
    {
        get_php_code('a(href=["a" => "b" => "c"])');
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 29
     */
    public function testAbsoluteIncludeWithNoBaseDir()
    {
        $jade = new Jade();
        $jade->render('include /auxiliary/world');
    }

    /**
     * @expectedException Jade\Parser\Exception
     * @expectedExceptionCode 10
     */
    public function testCannotBeReadFromPhp()
    {
        get_php_code('- var foo = Inf' . "\n" . 'p=foo');
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 34
     */
    public function testUnexpectedValue()
    {
        get_php_code('a(href="foo""bar")');
    }

    public function testUnexpectedValuePreviousException()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Not compatible with HHVM');
        }

        $code = null;
        try {
            get_php_code('a(href="foo""bar")');
        } catch (\Exception $e) {
            $code = $e->getPrevious()->getCode();
        }

        $this->assertSame(8, $code, 'Expected previous exception code should be 8 for UnexpectedValue.');
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 21
     */
    public function testUnableToFindAttributesClosingParenthesis()
    {
        get_php_code('a(href=');
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 24
     */
    public function testExpectedIndent()
    {
        get_php_code(':a()');
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 25
     */
    public function testUnexpectingToken()
    {
        get_php_code('a:' . "\n" . '!!!5');
    }

    /**
     * @expectedException EmulateBugException
     */
    public function testExceptionThroughtJade()
    {
        get_php_code('a(href=\JadeExceptionsTest::emulateBug())');
    }

    /**
     * @expectedException Jade\Parser\Exception
     * @expectedExceptionCode 10
     */
    public function testNonParsableExtends()
    {
        get_php_code(__DIR__ . '/../templates/auxiliary/extends-failure.jade');
    }

    /**
     * @expectedException EmulateBugException
     */
    public function testBrokenExtends()
    {
        get_php_code(__DIR__ . '/../templates/auxiliary/extends-exception.jade');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 3
     */
    public function testSetInvalidOption()
    {
        $jade = new Jade();
        $jade->setOption('i-do-not-exists', 'wrong');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 3
     */
    public function testSetInvalidOptions()
    {
        $jade = new Jade();
        $jade->setOptions(array(
            'prettyprint' => true,
            'i-do-not-exists' => 'right',
        ));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 2
     */
    public function testGetInvalidOption()
    {
        $jade = new Jade();
        $jade->getOption('i-do-not-exists');
    }

    /**
     * @expectedException EmulateBugException
     */
    public function testExtendsWithFilterException()
    {
        $jade = new Jade();
        $jade->filter('throw-exception', function () {
            throw new EmulateBugException("Bad filter", 1);
        });
        $jade->render(__DIR__ . '/../templates/auxiliary/extends-exception-filter.jade');
    }

    /**
     * Test OnlyOnceException
     */
    public function testExtendsWithParserException()
    {
        $parser = new ExtendParser(__DIR__ . '/../templates/auxiliary/extends-exception-filter.jade');
        $message = null;
        try {
            $parser->parse();
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        $this->assertTrue($message !== null, 'Extends with ExtendParser should throw an exception');
        $this->assertTrue(strpos($message, 'E: Works only once') !== false, 'Extends with ExtendParser should throw an exception with the initial message of the exception inside');
    }

    /**
     * Test OnlyOnceException
     */
    public function testIncludesWithParserException()
    {
        $parser = new IncludeParser(__DIR__ . '/../templates/auxiliary/include-exception-filter.jade');
        $message = null;
        try {
            $parser->parse();
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        $this->assertTrue($message !== null, 'Extends with IncludeParser should throw an exception');
        $this->assertTrue(strpos($message, 'I: Works only once') !== false, 'Include with IncludeParser should throw an exception with the initial message of the exception inside');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 17
     */
    public function testFilterDoesNotExist()
    {
        get_php_code(':foo' . "\n" . '  | Foo language');
    }

    /**
     * @expectedException EmulateBugException
     */
    public function testBrokenInclude()
    {
        get_php_code(__DIR__ . '/../templates/auxiliary/include-exception.jade');
    }
}
