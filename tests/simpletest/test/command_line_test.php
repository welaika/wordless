<?php
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../default_reporter.php');

class TestOfCommandLineParsing extends UnitTestCase
{
    public function testDefaultsToEmptyStringToMeanNullToTheSelectiveReporter()
    {
        $parser = new SimpleCommandLineParser(array());
        $this->assertIdentical($parser->getTest(), '');
        $this->assertIdentical($parser->getTestCase(), '');
    }
    
    public function testNotXmlByDefault()
    {
        $parser = new SimpleCommandLineParser(array());
        $this->assertFalse($parser->isXml());
    }
    
    public function testCanDetectRequestForXml()
    {
        $parser = new SimpleCommandLineParser(array('--xml'));
        $this->assertTrue($parser->isXml());
    }
    
    public function testCanReadAssignmentSyntax()
    {
        $parser = new SimpleCommandLineParser(array('--test=myTest'));
        $this->assertEqual($parser->getTest(), 'myTest');
    }
    
    public function testCanReadFollowOnSyntax()
    {
        $parser = new SimpleCommandLineParser(array('--test', 'myTest'));
        $this->assertEqual($parser->getTest(), 'myTest');
    }
    
    public function testCanReadShortForms()
    {
        $parser = new SimpleCommandLineParser(array('-t', 'myTest', '-c', 'MyClass', '-x'));
        $this->assertEqual($parser->getTest(), 'myTest');
        $this->assertEqual($parser->getTestCase(), 'MyClass');
        $this->assertTrue($parser->isXml());
    }
}
