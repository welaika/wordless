<?php

require_once dirname(__FILE__) . '/../autorun.php';

class ReferenceForTesting
{
}

class TestOfUnitTester extends UnitTestCase
{
    public function testAssertTrueReturnsAssertionAsBoolean()
    {
        $this->assertTrue($this->assertTrue(true));
    }
    
    public function testAssertFalseReturnsAssertionAsBoolean()
    {
        $this->assertTrue($this->assertFalse(false));
    }
    
    public function testAssertEqualReturnsAssertionAsBoolean()
    {
        $this->assertTrue($this->assertEqual(5, 5));
    }
    
    public function testAssertIdenticalReturnsAssertionAsBoolean()
    {
        $this->assertTrue($this->assertIdentical(5, 5));
    }
    
    public function testCoreAssertionsDoNotThrowErrors()
    {
        $this->assertIsA($this, 'UnitTestCase');
        $this->assertNotA($this, 'WebTestCase');
    }
    
    public function testReferenceAssertionOnObjects()
    {
        $a = new ReferenceForTesting();
        $b = $a;
        $this->assertSame($a, $b);
    }
    
    public function testReferenceAssertionOnScalars()
    {
        $a = 25;
        $b = &$a;
        $this->assertReference($a, $b);
    }
    
    public function testCloneOnObjects()
    {
        $a = new ReferenceForTesting();
        $b = new ReferenceForTesting();
        $this->assertClone($a, $b);
    }

    public function TODO_testCloneOnScalars()
    {
        $a = 25;
        $b = 25;
        $this->assertClone($a, $b);
    }

    public function testCopyOnScalars()
    {
        $a = 25;
        $b = 25;
        $this->assertCopy($a, $b);
    }
}
