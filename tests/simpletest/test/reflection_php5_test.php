<?php
// $Id$
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../reflection_php5.php');

class AnyOldLeafClass
{
    public function aMethod()
    {
    }
}

abstract class AnyOldClass
{
    public function aMethod()
    {
    }
}

class AnyOldLeafClassWithAFinal
{
    final public function aMethod()
    {
    }
}

interface AnyOldInterface
{
    public function aMethod();
}

interface AnyOldArgumentInterface
{
    public function aMethod(AnyOldInterface $argument);
}

interface AnyDescendentInterface extends AnyOldInterface
{
}

class AnyOldImplementation implements AnyOldInterface
{
    public function aMethod()
    {
    }
    public function extraMethod()
    {
    }
}

abstract class AnyAbstractImplementation implements AnyOldInterface
{
}

abstract class AnotherOldAbstractClass
{
    abstract protected function aMethod(AnyOldInterface $argument);
}

class AnyOldSubclass extends AnyOldImplementation
{
}

class AnyOldArgumentClass
{
    public function aMethod($argument)
    {
    }
}

class AnyOldArgumentImplementation implements AnyOldArgumentInterface
{
    public function aMethod(AnyOldInterface $argument)
    {
    }
}

class AnyOldTypeHintedClass implements AnyOldArgumentInterface
{
    public function aMethod(AnyOldInterface $argument)
    {
    }
}

class AnyDescendentImplementation implements AnyDescendentInterface
{
    public function aMethod()
    {
    }
}

class AnyOldOverloadedClass
{
    public function __isset($key)
    {
    }
    public function __unset($key)
    {
    }
}

class AnyOldClassWithStaticMethods
{
    public static function aStatic()
    {
    }
    public static function aStaticWithParameters($arg1, $arg2)
    {
    }
}

abstract class AnyOldAbstractClassWithAbstractMethods
{
    abstract public function anAbstract();
    abstract public function anAbstractWithParameter($foo);
    abstract public function anAbstractWithMultipleParameters($foo, $bar);
}

class TestOfReflection extends UnitTestCase
{
    public function testClassExistence()
    {
        $reflection = new SimpleReflection('AnyOldLeafClass');
        $this->assertTrue($reflection->classOrInterfaceExists());
        $this->assertTrue($reflection->classOrInterfaceExistsSansAutoload());
        $this->assertFalse($reflection->isAbstract());
        $this->assertFalse($reflection->isInterface());
    }

    public function testClassNonExistence()
    {
        $reflection = new SimpleReflection('UnknownThing');
        $this->assertFalse($reflection->classOrInterfaceExists());
        $this->assertFalse($reflection->classOrInterfaceExistsSansAutoload());
    }

    public function testDetectionOfAbstractClass()
    {
        $reflection = new SimpleReflection('AnyOldClass');
        $this->assertTrue($reflection->isAbstract());
    }

    public function testDetectionOfFinalMethods()
    {
        $reflection = new SimpleReflection('AnyOldClass');
        $this->assertFalse($reflection->hasFinal());
        $reflection = new SimpleReflection('AnyOldLeafClassWithAFinal');
        $this->assertTrue($reflection->hasFinal());
    }

    public function testFindingParentClass()
    {
        $reflection = new SimpleReflection('AnyOldSubclass');
        $this->assertEqual($reflection->getParent(), 'AnyOldImplementation');
    }

    public function testInterfaceExistence()
    {
        $reflection = new SimpleReflection('AnyOldInterface');
        $this->assertTrue($reflection->classOrInterfaceExists());
        $this->assertTrue($reflection->classOrInterfaceExistsSansAutoload());
        $this->assertTrue($reflection->isInterface());
    }

    public function testMethodsListFromClass()
    {
        $reflection = new SimpleReflection('AnyOldClass');
        $this->assertIdentical($reflection->getMethods(), array('aMethod'));
    }

    public function testMethodsListFromInterface()
    {
        $reflection = new SimpleReflection('AnyOldInterface');
        $this->assertIdentical($reflection->getMethods(), array('aMethod'));
        $this->assertIdentical($reflection->getInterfaceMethods(), array('aMethod'));
    }

    public function testMethodsComeFromDescendentInterfacesASWell()
    {
        $reflection = new SimpleReflection('AnyDescendentInterface');
        $this->assertIdentical($reflection->getMethods(), array('aMethod'));
    }
    
    public function testCanSeparateInterfaceMethodsFromOthers()
    {
        $reflection = new SimpleReflection('AnyOldImplementation');
        $this->assertIdentical($reflection->getMethods(), array('aMethod', 'extraMethod'));
        $this->assertIdentical($reflection->getInterfaceMethods(), array('aMethod'));
    }

    public function testMethodsComeFromDescendentInterfacesInAbstractClass()
    {
        $reflection = new SimpleReflection('AnyAbstractImplementation');
        $this->assertIdentical($reflection->getMethods(), array('aMethod'));
    }

    public function testInterfaceHasOnlyItselfToImplement()
    {
        $reflection = new SimpleReflection('AnyOldInterface');
        $this->assertEqual(
                $reflection->getInterfaces(),
                array('AnyOldInterface'));
    }

    public function testInterfacesListedForClass()
    {
        $reflection = new SimpleReflection('AnyOldImplementation');
        $this->assertEqual(
                $reflection->getInterfaces(),
                array('AnyOldInterface'));
    }

    public function testInterfacesListedForSubclass()
    {
        $reflection = new SimpleReflection('AnyOldSubclass');
        $this->assertEqual(
                $reflection->getInterfaces(),
                array('AnyOldInterface'));
    }

    public function testNoParameterCreationWhenNoInterface()
    {
        $reflection = new SimpleReflection('AnyOldArgumentClass');
        $function = $reflection->getSignature('aMethod');
        $this->assertEqual('function aMethod($argument)', $function);
    }

    public function testParameterCreationWithoutTypeHinting()
    {
        $reflection = new SimpleReflection('AnyOldArgumentImplementation');
        $function = $reflection->getSignature('aMethod');
        $this->assertEqual('function aMethod(AnyOldInterface $argument)', $function);
    }

    public function testParameterCreationForTypeHinting()
    {
        $reflection = new SimpleReflection('AnyOldTypeHintedClass');
        $function = $reflection->getSignature('aMethod');
        $this->assertEqual('function aMethod(AnyOldInterface $argument)', $function);
    }

    public function testIssetFunctionSignature()
    {
        $reflection = new SimpleReflection('AnyOldOverloadedClass');
        $function = $reflection->getSignature('__isset');
        $this->assertEqual('function __isset($key)', $function);
    }
    
    public function testUnsetFunctionSignature()
    {
        $reflection = new SimpleReflection('AnyOldOverloadedClass');
        $function = $reflection->getSignature('__unset');
        $this->assertEqual('function __unset($key)', $function);
    }

    public function testProperlyReflectsTheFinalInterfaceWhenObjectImplementsAnExtendedInterface()
    {
        $reflection = new SimpleReflection('AnyDescendentImplementation');
        $interfaces = $reflection->getInterfaces();
        $this->assertEqual(1, count($interfaces));
        $this->assertEqual('AnyDescendentInterface', array_shift($interfaces));
    }
    
    public function testCreatingSignatureForAbstractMethod()
    {
        $reflection = new SimpleReflection('AnotherOldAbstractClass');
        $this->assertEqual($reflection->getSignature('aMethod'), 'function aMethod(AnyOldInterface $argument)');
    }
    
    public function testCanProperlyGenerateStaticMethodSignatures()
    {
        $reflection = new SimpleReflection('AnyOldClassWithStaticMethods');
        $this->assertEqual('static function aStatic()', $reflection->getSignature('aStatic'));
        $this->assertEqual(
            'static function aStaticWithParameters($arg1, $arg2)',
            $reflection->getSignature('aStaticWithParameters')
        );
    }
}

class TestOfReflectionWithTypeHints extends UnitTestCase
{
    public function testParameterCreationForTypeHintingWithArray()
    {
        eval('interface AnyOldArrayTypeHintedInterface {
				  function amethod(array $argument);
			  } 
			  class AnyOldArrayTypeHintedClass implements AnyOldArrayTypeHintedInterface {
				  function amethod(array $argument) {}
			  }');
        $reflection = new SimpleReflection('AnyOldArrayTypeHintedClass');
        $function = $reflection->getSignature('amethod');
        $this->assertEqual('function amethod(array $argument)', $function);
    }
}

class TestOfAbstractsWithAbstractMethods extends UnitTestCase
{
    public function testCanProperlyGenerateAbstractMethods()
    {
        $reflection = new SimpleReflection('AnyOldAbstractClassWithAbstractMethods');
        $this->assertEqual(
            'function anAbstract()',
            $reflection->getSignature('anAbstract')
        );
        $this->assertEqual(
            'function anAbstractWithParameter($foo)',
            $reflection->getSignature('anAbstractWithParameter')
        );
        $this->assertEqual(
            'function anAbstractWithMultipleParameters($foo, $bar)',
            $reflection->getSignature('anAbstractWithMultipleParameters')
        );
    }
}
