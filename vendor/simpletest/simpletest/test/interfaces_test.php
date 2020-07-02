<?php

require_once __DIR__ . '/../autorun.php';
include(__DIR__ . '/support/spl_examples.php');

interface DummyInterface
{
    public function aMethod();
    public function anotherMethod($a);
    public function &referenceMethod(&$a);
}

Mock::generate('DummyInterface');
Mock::generatePartial('DummyInterface', 'PartialDummyInterface', array());

class TestOfMockInterfaces extends UnitTestCase
{
    public function testCanMockAnInterface()
    {
        $mock = new MockDummyInterface();
        $this->assertIsA($mock, 'SimpleMock');
        $this->assertIsA($mock, 'MockDummyInterface');
        $this->assertTrue(method_exists($mock, 'aMethod'));
        $this->assertTrue(method_exists($mock, 'anotherMethod'));
        $this->assertNull($mock->aMethod());
    }

    public function testMockedInterfaceExpectsParameters()
    {
        $mock = new MockDummyInterface();
        $this->expectError();
        try {
            $mock->anotherMethod();
        } catch (Error $e) {
            trigger_error($e->getMessage());
        }
    }

    public function testCannotPartiallyMockAnInterface()
    {
        $this->assertFalse(class_exists('PartialDummyInterface'));
    }
}

class TestOfSpl extends UnitTestCase
{
    public function testCanMockAllSplClasses()
    {
        static $classesToExclude = [
            'SplHeap', // the method compare() is missing
            'FilterIterator', // the method accept() is missing
            'RecursiveFilterIterator' // the method hasChildren() must contain body
        ];

        foreach (spl_classes() as $class) {

            // exclude classes
            if (in_array($class, $classesToExclude)) {
                continue;
            }

            $mock_class = "Mock$class";
            Mock::generate($class);
            $this->assertIsA(new $mock_class(), $mock_class);
        }
    }

    public function testExtensionOfCommonSplClasses()
    {
        Mock::generate('IteratorImplementation');
        $this->assertIsA(
                new IteratorImplementation(),
                'IteratorImplementation');
        Mock::generate('IteratorAggregateImplementation');
        $this->assertIsA(
                new IteratorAggregateImplementation(),
                'IteratorAggregateImplementation');
    }
}

class WithHint
{
    public function hinted(DummyInterface $object)
    {
    }
}

class ImplementsDummy implements DummyInterface
{
    public function aMethod()
    {
    }
    public function anotherMethod($a)
    {
    }
    public function &referenceMethod(&$a)
    {
    }
    public function extraMethod($a = false)
    {
    }
}
Mock::generate('ImplementsDummy');

class TestOfImplementations extends UnitTestCase
{
    public function testMockedInterfaceCanPassThroughTypeHint()
    {
        $mock   = new MockDummyInterface();
        $hinter = new WithHint();
        $hinter->hinted($mock);
    }

    public function testImplementedInterfacesAreCarried()
    {
        $mock   = new MockImplementsDummy();
        $hinter = new WithHint();
        $hinter->hinted($mock);
    }

    public function testNoSpuriousWarningsWhenSkippingDefaultedParameter()
    {
        $mock = new MockImplementsDummy();
        $mock->extraMethod();
    }
}

interface SampleInterfaceWithClone
{
    public function __clone();
}

class TestOfSampleInterfaceWithClone extends UnitTestCase
{
    public function testCanMockWithoutErrors()
    {
        Mock::generate('SampleInterfaceWithClone');
    }
}

interface SampleInterfaceWithHintInSignature
{
    public function method(array $hinted);
}

class TestOfInterfaceMocksWithHintInSignature extends UnitTestCase
{
    public function testBasicConstructOfAnInterfaceWithHintInSignature()
    {
        Mock::generate('SampleInterfaceWithHintInSignature');
        $mock = new MockSampleInterfaceWithHintInSignature();
        $this->assertIsA($mock, 'SampleInterfaceWithHintInSignature');
    }
}
