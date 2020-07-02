<?php

/**
 * Persists code coverage data into SQLite database and aggregate data for convienent
 * interpretation in report generator.  Be sure to not to keep an instance longer
 * than you have, otherwise you risk overwriting database edits from another process
 * also trying to make updates.
 */
class CoverageDataHandler
{
    public $db;

    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->db       = new SQLite3($filename);
        if (empty($this->db)) {
            throw new Exception('Could not create SQLite DB ' . $filename);
        }
    }

    public function createSchema()
    {
        $this->db->query('CREATE TABLE untouched (filename text)');
        $this->db->query('CREATE TABLE coverage (name text, coverage text)');
    }

    public function getFilenames()
    {
        $filenames = array();
        $cursor    = $this->db->query('SELECT DISTINCT name FROM coverage');
        while ($row = $cursor->fetchArray()) {
            $filenames[] = $row[0];
        }

        return $filenames;
    }

    public function write($coverage)
    {
        foreach ($coverage as $file => $lines) {
            $coverageStr      = serialize($lines);
            $relativeFilename = self::ltrim(getcwd() . '/', $file);
            $sql              = "INSERT INTO coverage (name, coverage) VALUES ('$relativeFilename', '$coverageStr')";
            # if this fails, check you have write permission
            $this->db->query($sql);
        }
    }

    public function read()
    {
        $coverage = array_flip($this->getFilenames());
        foreach ($coverage as $file => $garbage) {
            $coverage[$file] = $this->readFile($file);
        }

        return $coverage;
    }

    public function readFile($file)
    {
        $aggregate = array();
        $sql       = "SELECT coverage FROM coverage WHERE name = '$file'";
        $result    = $this->db->query($sql);
        while ($row = $result->fetchArray()) {
            $this->aggregateCoverage($aggregate, unserialize($row[0]));
        }

        return $aggregate;
    }

    public function aggregateCoverage(&$total, $next)
    {
        foreach ($next as $lineno => $code) {
            if (!isset($total[$lineno])) {
                $total[$lineno] = $code;
            } else {
                $total[$lineno] = $this->aggregateCoverageCode($total[$lineno], $code);
            }
        }
    }

    public function aggregateCoverageCode($code1, $code2)
    {
        switch ($code1) {
            case -2: return -2;
            case -1: return $code2;
            default:
                switch ($code2) {
                    case -2: return -2;
                    case -1: return $code1;
                }
        }

        return $code1 + $code2;
    }

    public static function ltrim($cruft, $pristine)
    {
        if (stripos($pristine, $cruft) === 0) {
            return substr($pristine, strlen($cruft));
        }

        return $pristine;
    }

    public function writeUntouchedFile($file)
    {
        $relativeFile = self::ltrim('./', $file);
        $sql          = "INSERT INTO untouched values ('$relativeFile')";
        $this->db->query($sql);
    }

    public function readUntouchedFiles()
    {
        $untouched = array();
        $result    = $this->db->query('SELECT filename FROM untouched ORDER BY filename');
        while ($row = $result->fetchArray()) {
            $untouched[] = $row[0];
        }

        return $untouched;
    }
}
