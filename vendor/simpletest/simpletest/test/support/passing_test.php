<?php

require_once __DIR__ . '/../../autorun.php';

class PassingTest extends UnitTestCase
{
    public function test_pass()
    {
        $this->assertEqual(2, 2);
    }
}
