<?php
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/support/test1.php');

class TestOfAutorun extends UnitTestCase
{
    public function testLoadIfIncluded()
    {
        $tests = new TestSuite();
        $tests->addFile(dirname(__FILE__) . '/support/test1.php');
        $this->assertEqual($tests->getSize(), 1);
    }

    public function testExitStatusOneIfTestsFail()
    {
        exec('php ' . dirname(__FILE__) . '/support/failing_test.php', $output, $exit_status);
        $this->assertEqual($exit_status, 1);
    }

    public function testExitStatusZeroIfTestsPass()
    {
        exec('php ' . dirname(__FILE__) . '/support/passing_test.php', $output, $exit_status);
        $this->assertEqual($exit_status, 0);
    }
}
