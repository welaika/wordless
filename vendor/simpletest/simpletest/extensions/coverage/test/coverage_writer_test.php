<?php

require_once __DIR__ . '/../../../autorun.php';

class CoverageWriterTest extends UnitTestCase
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
        require_once __DIR__ . '/../coverage_writer.php';
        require_once __DIR__ . '/../coverage_calculator.php';
    }

    public function testGenerateSummaryReport()
    {
        $writer             = new CoverageWriter();
        $coverage           = array('file' => array(0, 1));
        $untouched          = array('missed-file');
        $calc               = new CoverageCalculator();
        $variables          = $calc->variables($coverage, $untouched);
        $variables['title'] = 'Coverage Summary';
        $reportFile         = __DIR__ . '/summaryReport.html';

        $contents = $writer->writeSummaryReport($reportFile, $variables);

        $dom = new SimpleXMLElement($contents);

        $totalPercentCoverage = $dom->xpath("//span[@class='totalPercentCoverage']");
        $this->assertEqual('50%', (string) $totalPercentCoverage[0]);

        $fileLinks    = $dom->xpath("//a[@class='fileReportLink']");
        $fileLinkAttr = $fileLinks[0]->attributes();
        $this->assertEqual('file.html', $fileLinkAttr['href']);
        $this->assertEqual('file', (string) ($fileLinks[0]));

        $untouchedFile = $dom->xpath("//span[@class='untouchedFile']");
        $this->assertEqual('missed-file', (string) $untouchedFile[0]);

        unlink($reportFile);
    }

    public function testGenerateCoverageByFile()
    {
        $writer             = new CoverageWriter();
        $cov                = array(3 => 1, 4 => -2); // 2 comments, 1 code, 1 dead  (1-based indexes)
        $coverageSampleFile = __DIR__ . '/sample/code.php';
        $calc               = new CoverageCalculator();
        $variables          = $calc->coverageByFileVariables($coverageSampleFile, $cov);
        $variables['title'] = 'File Coverage';
        $reportFile         = __DIR__ . '/sampleFileReport.html';

        $contents = $writer->writeFileReport($reportFile, $variables);

        $dom = new SimpleXMLElement($contents);

        $cells = $dom->xpath("//table[@id='code']/tbody/tr/td/span");
        $this->assertEqual('comment code', self::getAttribute($cells[1], 'class'));
        $this->assertEqual('comment code', self::getAttribute($cells[3], 'class'));
        $this->assertEqual('covered code', self::getAttribute($cells[5], 'class'));
        $this->assertEqual('dead code', self::getAttribute($cells[7], 'class'));

        unlink($reportFile);
    }

    public static function getAttribute($element, $attribute)
    {
        return $element->attributes()[$attribute];
    }
}
