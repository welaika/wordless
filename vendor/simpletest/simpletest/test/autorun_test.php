<?php

require_once __DIR__ . '/../autorun.php';
require_once __DIR__ . '/support/test1.php';

class TestOfAutorun extends UnitTestCase
{
    public function testLoadIfIncluded()
    {
        $tests = new TestSuite();
        $tests->addFile(__DIR__ . '/support/test1.php');
        $this->assertEqual($tests->getSize(), 1);
    }

    public function testExitStatusOneIfTestsFail()
    {
        exec('php ' . __DIR__ . '/support/failing_test.php', $output, $exit_status);
        $this->assertEqual($exit_status, 1);
    }

    public function testExitStatusZeroIfTestsPass()
    {
        exec('php ' . __DIR__ . '/support/passing_test.php', $output, $exit_status);
        $this->assertEqual($exit_status, 0);
    }
}
