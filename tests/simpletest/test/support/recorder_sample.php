<?php

require_once dirname(__FILE__) . '/../../autorun.php';

class SampleTestForRecorder extends UnitTestCase
{
    public function testTrueIsTrue()
    {
        $this->assertTrue(true);
    }

    public function testFalseIsTrue()
    {
        $this->assertFalse(true);
    }
}
