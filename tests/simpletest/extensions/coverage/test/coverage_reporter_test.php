<?php
require_once(dirname(__FILE__) . '/../../../autorun.php');

class CoverageReporterTest extends UnitTestCase
{
    public function skip()
    {
        $this->skipIf(
                !file_exists('DB/sqlite.php'),
                'The Coverage extension needs to have PEAR installed');
    }
    
    public function setUp()
    {
        require_once dirname(__FILE__) .'/../coverage_reporter.php';
        new CoverageReporter();
    }

    public function testreportFilename()
    {
        $this->assertEqual("parula.php.html", CoverageReporter::reportFilename("parula.php"));
        $this->assertEqual("warbler_parula.php.html", CoverageReporter::reportFilename("warbler/parula.php"));
        $this->assertEqual("warbler_parula.php.html", CoverageReporter::reportFilename("warbler\\parula.php"));
    }
}
