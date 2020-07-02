<?php

require_once __DIR__ . '/../../../autorun.php';
require_once __DIR__ . '/../PHPUnitTestCase.php';

class SameTestClass
{
}

class TestOfPHPUnitAdapter extends PHPUnitTestCase
{
    public function testBoolean()
    {
        $this->assertTrue(true, 'PHPUnit true');
        $this->assertFalse(false, 'PHPUnit false');
    }

    public function testName()
    {
        $this->assertTrue($this->getName() === get_class($this));
    }

    public function testPass()
    {
        $this->pass('PHPUnit pass');
    }

    public function testNulls()
    {
        $value = null;
        $this->assertNull($value, 'PHPUnit null');
        $value = 0;
        $this->assertNotNull($value, 'PHPUnit not null');
    }

    public function testType()
    {
        $this->assertType('Hello', 'string', 'PHPUnit type');
    }

    public function testEquals()
    {
        $this->assertEquals(12, 12, 'PHPUnit identity');
        $this->setLooselyTyped(true);
        $this->assertEquals('12', 12, 'PHPUnit equality');
    }

    public function testSame()
    {
        $same = new SameTestClass();
        $this->assertSame($same, $same, 'PHPUnit same');
    }

    public function testRegExp()
    {
        $this->assertRegExp('/hello/', 'A big hello from me', 'PHPUnit regex');
    }
}
