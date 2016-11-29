<?php
// $Id$
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../collector.php');

class ExtensionsTests extends TestSuite
{
    public function ExtensionsTests()
    {
        $this->TestSuite('Extension tests for SimpleTest ' . SimpleTest::getVersion());

        $nodes = new RecursiveDirectoryIterator(dirname(__FILE__).'/../extensions/');
        foreach (new RecursiveIteratorIterator($nodes) as $node) {
            if (preg_match('/test\.php$/', $node->getFilename())) {
                $this->addFile($node->getPathname());
            }
        }
    }
}
