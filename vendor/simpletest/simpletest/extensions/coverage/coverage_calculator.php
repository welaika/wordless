<?php

require_once __DIR__ . '/coverage_utils.php';

class CoverageCalculator
{
    public function coverageByFileVariables($file, $coverage)
    {
        $hnd = fopen($file, 'r');
        if (!$hnd) {
            throw new Exception("File $file is missing");
        }
        $lines = [];
        for ($i = 1; !feof($hnd); $i++) {
            $line         = fgets($hnd);
            $lineCoverage = $this->lineCoverageCodeToStyleClass($coverage, $i);
            $lines[$i]    = ['lineCoverage' => $lineCoverage, 'code' => $line];
        }

        fclose($hnd);

        return ['file' => $file, 'lines' => $lines, 'coverage' => $coverage];
    }

    public function lineCoverageCodeToStyleClass($coverage, $line)
    {
        if (!array_key_exists($line, $coverage)) {
            return 'comment';
        }
        $code = $coverage[$line];
        if (empty($code)) {
            return 'comment';
        }
        switch ($code) {
            case -1:
                return 'missed';
            case -2:
                return 'dead';
        }

        return 'covered';
    }

    public function totalLinesOfCode($total, $coverage)
    {
        return $total + count($coverage);
    }

    /**
     *
     * https://xdebug.org/docs/code_coverage
     *
     * 1: this line was executed
     * -1: this line was not executed
     * -2: this line did not have executable code on it
     *
     * @param type $total
     * @param type $line
     * @return type
     */
    public function lineCoverage($total, $line)
    {
        return $total + ($line > 0 || $line == -2 ? 1 : 0);
    }

    public function totalCoverage($total, $coverage)
    {
        return $total + array_reduce($coverage, array($this, 'lineCoverage'));
    }

    public function percentCoverageForFile($file, $coverage)
    {
        $fileReport = CoverageUtils::reportFilename($file);

        $loc = count($coverage);
        if ($loc == 0) {
            return 0;
        }
        $lineCoverage      = array_reduce($coverage, array($this, 'lineCoverage'));
        $percentage        = 100 * ($lineCoverage / $loc);
        return ['fileReport' => $fileReport, 'percentage' => $percentage];
    }

    public function variables($coverage, $untouched)
    {
        $coverageByFile = [];
        foreach($coverage as $file => $lineCoverageData) {
            $coverageByFile[$file] = $this->percentCoverageForFile($file, $lineCoverageData);
        }

        $totalLinesOfCode = array_reduce($coverage, [$this, 'totalLinesOfCode']);

        if ($totalLinesOfCode > 0) {
            $totalLinesOfCoverage = array_reduce($coverage, array($this, 'totalCoverage'));
            $totalPercentCoverage = 100 * ($totalLinesOfCoverage / $totalLinesOfCode);
        }

        $untouchedPercentageDenominator = count($coverage) + count($untouched);
        if ($untouchedPercentageDenominator > 0) {
            $filesTouchedPercentage = 100 * count($coverage) / $untouchedPercentageDenominator;
        }

        return [
            'coverageByFile'         => $coverageByFile,
            'totalPercentCoverage'   => $totalPercentCoverage,
            'totalLinesOfCode'       => $totalLinesOfCode,
            'totalLinesOfCoverage'   => $totalLinesOfCoverage,
            'filesTouchedPercentage' => $filesTouchedPercentage,
            'untouched'              => $untouched
        ];
    }
}
