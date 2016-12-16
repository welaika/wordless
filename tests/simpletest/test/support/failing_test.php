<?php
require_once(dirname(__FILE__) . '/../../autorun.php');

class FailingTest extends UnitTestCase
{
    public function test_fail()
    {
        $this->assertEqual(1, 2);
    }
}
