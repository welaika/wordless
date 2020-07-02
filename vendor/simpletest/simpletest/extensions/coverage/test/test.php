<?php

require_once __DIR__ . '/../../../autorun.php';

class CoverageUnitTests extends TestSuite
{
    public function __construct()
    {
        parent::__construct('Coverage Unit Tests');

        $path  = __DIR__ . '/*_test.php';
        $files = glob($path);

        foreach ($files as $test) {
            $this->addFile($test);
        }
    }
}
