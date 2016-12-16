<?php
// $Id: test.php 1500 2007-04-29 14:33:31Z pp11 $
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../recorder.php');

class TestOfRecorder extends UnitTestCase
{
    public function testContentOfRecorderWithOnePassAndOneFailure()
    {
        $test = new TestSuite();
        $test->addFile(dirname(__FILE__) . '/support/recorder_sample.php');
        $recorder = new Recorder(new SimpleReporter());
        $test->run($recorder);
        $this->assertEqual(count($recorder->results), 2);
        $this->assertIsA($recorder->results[0], 'SimpleResultOfPass');
        $this->assertEqual('testTrueIsTrue', array_pop($recorder->results[0]->breadcrumb));
        $this->assertPattern('/ at \[.*\Wrecorder_sample\.php line 9\]/', $recorder->results[0]->message);
        $this->assertIsA($recorder->results[1], 'SimpleResultOfFail');
        $this->assertEqual('testFalseIsTrue', array_pop($recorder->results[1]->breadcrumb));
        $this->assertPattern("/Expected false, got \[Boolean: true\] at \[.*\Wrecorder_sample\.php line 14\]/",
                             $recorder->results[1]->message);
    }
}
