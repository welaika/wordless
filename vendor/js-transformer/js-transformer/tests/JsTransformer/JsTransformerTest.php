<?php

namespace Test\JsTransformer;

use JsTransformer\JsTransformer;
use JsTransformer\NodeEngine;
use NodejsPhpFallback\NodejsPhpFallback;
use PHPUnit_Framework_TestCase;

class JsTransformerTest extends PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        NodejsPhpFallback::installPackages(array(
            'jstransformer-less' => '2.3.0',
        ));

        $transformer = new JsTransformer();

        $result = trim($transformer->call('jstransformer-less', array(
            "a {\n  .foo {\n    color: red;\n  }\n}\n",
            array(
                'compress' => false,
            ),
        )));

        self::assertSame("a .foo {\n  color: red;\n}", $result);

        $result = trim($transformer->call('jstransformer-less', array(
            "a {\n  .foo {\n    color: red;\n  }\n}\n",
            array(
                'compress' => true,
            ),
        )));

        self::assertSame("a .foo{color:red}", $result);
    }

    public function testGetNodeEngine()
    {
        $transformer = new JsTransformer();

        self::assertInstanceOf('JsTransformer\\NodeEngine', $transformer->getNodeEngine());
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage it-does-not-exists seems to not be installed.
     */
    public function testNonExistingPackage()
    {
        $transformer = new JsTransformer();

        $transformer->call('it-does-not-exists', array());
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage node is required to get jstransformer-less to work.
     */
    public function testNodeMissing()
    {
        $transformer = new JsTransformer();
        $transformer->getNodeEngine()->setNodePath('/this/path/is/invalid/%%%%%');

        $transformer->call('jstransformer-less', array());
    }
}
