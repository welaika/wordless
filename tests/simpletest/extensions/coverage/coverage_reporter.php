<?php

require_once __DIR__ . '/coverage_calculator.php';
require_once __DIR__ . '/coverage_utils.php';
require_once __DIR__ . '/coverage_writer.php';

/**
 * Take aggregated coverage data and generate reports from it.
 */
class CoverageReporter
{
    public $coverage;
    public $untouched;
    public $reportDir;
    public $title = 'Coverage';
    public $writer;
    public $calculator;
    public $summaryFile;

    public function __construct()
    {
        $this->writer     = new CoverageWriter();
        $this->calculator = new CoverageCalculator();

        $this->summaryFile = $this->reportDir . '/index.html';
    }

    public function generate()
    {
        echo 'Generating Code Coverage Report';

        CoverageUtils::mkdir($this->reportDir);

        $this->generateSummaryReport();

        foreach ($this->coverage as $file => $cov) {
            $this->generateCoverageByFile($file, $cov);
        }

        echo "Report generated: $this->summaryFile\n";
    }

    public function generateSummaryReport()
    {
        $variables          = $this->calculator->variables($this->coverage, $this->untouched);
        $variables['title'] = $this->title;

        $this->writer->writeSummaryReport($this->summaryFile, $variables);
    }

    public function generateCoverageByFile($file, $cov)
    {
        $reportFile = $this->reportDir . '/' . CoverageUtils::reportFilename($file);

        $variables          = $this->calculator->coverageByFileVariables($file, $cov);
        $variables['title'] = $this->title . ' - ' . $file;

        $this->writer->writeFileReport($reportFile, $variables);
    }
}
