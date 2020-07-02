<?php

require_once __DIR__ . '/../autorun.php';

class AllTests extends TestSuite
{
    public function __construct()
    {
        parent::__construct('All tests for SimpleTest ' . SimpleTest::getVersion());
        $this->addFile(__DIR__ . '/unit_tests.php');
        $this->addFile(__DIR__ . '/shell_test.php');

        /**
         * The "live" and "acceptance" tests require a running local webserver on "localhost:8080".
         * We are using PHP's built-in webserver to serve the "test/site".
         * The start command for the server is: `php -S localhost:8080 -t test/site`.
         */
        $this->addFile(__DIR__ . '/live_test.php');
        $this->addFile(__DIR__ . '/acceptance_test.php');
    }
}
