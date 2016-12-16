<?php
// $Id$
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../collector.php');
SimpleTest::ignore('MockTestSuite');
Mock::generate('TestSuite');

class PathEqualExpectation extends EqualExpectation
{
    public function __construct($value, $message = '%s')
    {
        parent::__construct(str_replace("\\", '/', $value), $message);
    }

    public function test($compare)
    {
        return parent::test(str_replace("\\", '/', $compare));
    }
}

class TestOfCollector extends UnitTestCase
{
    public function testCollectionIsAddedToGroup()
    {
        $suite = new MockTestSuite();
        $suite->expectMinimumCallCount('addFile', 2);
        $suite->expect(
                'addFile',
                array(new PatternExpectation('/collectable\\.(1|2)$/')));
        $collector = new SimpleCollector();
        $collector->collect($suite, dirname(__FILE__) . '/support/collector/');
    }
}

class TestOfPatternCollector extends UnitTestCase
{
    public function testAddingEverythingToGroup()
    {
        $suite = new MockTestSuite();
        $suite->expectCallCount('addFile', 2);
        $suite->expect(
                'addFile',
                array(new PatternExpectation('/collectable\\.(1|2)$/')));
        $collector = new SimplePatternCollector('/.*/');
        $collector->collect($suite, dirname(__FILE__) . '/support/collector/');
    }

    public function testOnlyMatchedFilesAreAddedToGroup()
    {
        $suite = new MockTestSuite();
        $suite->expectOnce('addFile', array(new PathEqualExpectation(
                dirname(__FILE__) . '/support/collector/collectable.1')));
        $collector = new SimplePatternCollector('/1$/');
        $collector->collect($suite, dirname(__FILE__) . '/support/collector/');
    }
}
