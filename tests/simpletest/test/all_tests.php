<?php

require_once dirname(__FILE__) . '/../autorun.php';

class AllTests extends TestSuite
{
    public function __construct()
    {
        parent::__construct('All tests for SimpleTest ' . SimpleTest::getVersion());
        $this->addFile(dirname(__FILE__) . '/unit_tests.php');
        $this->addFile(dirname(__FILE__) . '/shell_test.php');
        $this->addFile(dirname(__FILE__) . '/live_test.php');
        
        // jakoch: disabled acceptance tests. 
        // because, we will not test against a live server over the network.
        //$this->addFile(dirname(__FILE__) . '/acceptance_test.php');
    }
}
