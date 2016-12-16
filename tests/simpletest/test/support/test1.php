<?php
class test1 extends UnitTestCase
{
    public function test_pass()
    {
        $this->assertEqual(3, 1+2, "pass1");
    }
}
