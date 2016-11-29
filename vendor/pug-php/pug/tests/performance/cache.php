<?php

use Jade\Jade;

class JadeCachePerformanceTest extends PHPUnit_Framework_TestCase
{
    protected function getPerformanceTemplate($template)
    {
        return TEMPLATES_DIRECTORY . DIRECTORY_SEPARATOR . 'performance' . DIRECTORY_SEPARATOR . $template . '.jade';
    }

    protected function getPhpFromTemplate($template)
    {
        return $this->getPhp(file_get_contents($this->getPerformanceTemplate($template)));
    }

    protected function getPhp($template)
    {
        $jade = new Jade(array(
            'singleQuote' => false,
            'prettyprint' => false,
            'restrictedScope' => true,
        ));

        return trim($jade->compile($template));
    }

    protected function getHtmlFromTemplate($template, array $vars = array())
    {
        $jade = new Jade(array(
            'singleQuote' => false,
            'prettyprint' => false,
            'restrictedScope' => true,
        ));

        return trim($jade->render($this->getPerformanceTemplate($template), $vars));
    }

    /**
     * Cache weight.
     */
    public function testCacheWeihgt()
    {
        $this->assertSame('<p>Hello world!</p>', $this->getPhp('p Hello world!'), 'Simple template should output simple code.');
    }
}
