<?php

use JsPhpize\JsPhpize;

class RenderTest extends \PHPUnit_Framework_TestCase
{
    public function caseProvider()
    {
        $cases = array();

        $examples = __DIR__ . '/../examples';
        foreach (scandir($examples) as $file) {
            if (substr($file, -7) === '.return') {
                $cases[] = array($file, substr($file, 0, -7) . '.js');
            }
        }

        return $cases;
    }

    /**
     * @dataProvider caseProvider
     */
    public function testJsPhpizeGeneration($returnFile, $jsFile)
    {
        $examples = __DIR__ . '/../examples';
        $jsPhpize = new JsPhpize();
        $expected = file_get_contents($examples . '/' . $returnFile);
        $result = $jsPhpize->render($examples . '/' . $jsFile);

        $expected = str_replace("\r", '', trim($expected));
        $actual = str_replace("\r", '', trim($result));

        $this->assertSame($expected, $actual, $jsFile . ' should render ' . $expected);
    }
}
