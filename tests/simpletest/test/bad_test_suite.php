<?php

require_once __DIR__ . '/../autorun.php';

class BadTestCases extends TestSuite
{
    public function __construct()
    {
        $this->TestSuite('Two bad test cases');
        $this->addFile(__DIR__ . '/support/empty_test_file.php');
    }
}
