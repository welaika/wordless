<?php

require_once __DIR__ . '/../../../autorun.php';

class CoverageReporterTest extends UnitTestCase
{
    public function skip()
    {
        $this->skipIf(
            !extension_loaded('sqlite3'),
            'The Coverage extension requires the PHP extension "php_sqlite3".'
        );
    }

    public function setUp()
    {
        require_once __DIR__ . '/../coverage_reporter.php';
        new CoverageReporter();
    }
}
