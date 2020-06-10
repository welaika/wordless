<?php

require_once __DIR__ . '/../autorun.php';

class ReferenceForTesting
{
    private $reference;
    public function setReference(&$reference)
    {
        $this->reference = $reference;
    }
    public function &getReference()
    {
        return $this->reference;
    }
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
        $b = &$a; // reference is a pointer to a scalar
        $this->assertReference($a, $b);
    }

    public function testReferenceAssertionOnObject()
    {
        $refValue = 5;
        $a = new ReferenceForTesting();
        $a->setReference($refValue);
        $b = &$a->getReference(); // $b is a reference to $a->reference, which is 5.
        $this->assertReference($a->getReference(), $b);
    }

    public function testCloneOnObjects()
    {
        $a = new ReferenceForTesting();
        $b = new ReferenceForTesting();
        $this->assertClone($a, $b);
    }

    /**
     * @todo
     * http://php.net/manual/de/function.is-scalar.php
     */
    /*public function testCloneOnScalars()
    {
        $this->assertClone(20, 20);       // int
        $this->assertClone(20.2, 20.2);   // float
        $this->assertClone("abc", "abc"); // string
        $this->assertClone(true, true);   // bool
    }*/

    public function testCopyOnScalars()
    {
        $a = 25;
        $b = 25;
        $this->assertCopy($a, $b);
    }

    public function testEscapePercentageSignsExceptFirst()
    {
        $a = 'http://www.domain.com/some%20long%%20name.html';
        $b = $this->escapePercentageSignsExceptFirst('http://www.domain.com/some%20long%20name.html');
        $this->assertEqual($a, $b);
    }

}
