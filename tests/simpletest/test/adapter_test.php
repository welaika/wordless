<?php
// $Id$
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../extensions/pear_test_case.php');

class SameTestClass
{
}

class TestOfPearAdapter extends PHPUnit_TestCase
{
    public function testBoolean()
    {
        $this->assertTrue(true, "PEAR true");
        $this->assertFalse(false, "PEAR false");
    }
    
    public function testName()
    {
        $this->assertTrue($this->getName() == get_class($this));
    }
    
    public function testPass()
    {
        $this->pass("PEAR pass");
    }
    
    public function testNulls()
    {
        $value = null;
        $this->assertNull($value, "PEAR null");
        $value = 0;
        $this->assertNotNull($value, "PEAR not null");
    }
    
    public function testType()
    {
        $this->assertType("Hello", "string", "PEAR type");
    }
    
    public function testEquals()
    {
        $this->assertEquals(12, 12, "PEAR identity");
        $this->setLooselyTyped(true);
        $this->assertEquals("12", 12, "PEAR equality");
    }
    
    public function testSame()
    {
        $same = new SameTestClass();
        $this->assertSame($same, $same, "PEAR same");
    }
    
    public function testRegExp()
    {
        $this->assertRegExp('/hello/', "A big hello from me", "PEAR regex");
    }
}
