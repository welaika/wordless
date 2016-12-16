<?php
require_once(dirname(__FILE__) . '/../../autorun.php');

class PassingTest extends UnitTestCase
{
    public function test_pass()
    {
        $this->assertEqual(2, 2);
    }
}
