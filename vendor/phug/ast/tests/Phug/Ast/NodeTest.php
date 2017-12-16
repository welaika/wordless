<?php

namespace Phug\Test\Ast;

use Phug\Ast\Node;
use Phug\Ast\NodeInterface;
use Phug\AstException;

//@codingStandardsIgnoreStart
class A extends Node
{
}
class B extends Node
{
}
class C extends Node
{
}
class D extends Node
{
}

/**
 * @coversDefaultClass Phug\Ast\Node
 */
class NodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getParent
     * @covers ::getChildren
     */
    public function testConstructor()
    {
        $a = new A();
        self::assertInstanceOf(Node::class, $a);

        $b = new B($a);
        self::assertSame($a, $b->getParent());
        self::assertSame([$b], $a->getChildren());

        $d = new D(null, [$a, $c = new C()]);
        self::assertSame($d, $a->getParent());
        self::assertSame($d, $c->getParent());
        self::assertSame([$a, $c], $d->getChildren());
    }

    /**
     * @covers ::__clone
     * @covers ::getChildren
     */
    public function testClone()
    {
        $a = new A(null, [$b = new B(), $c = new C()]);
        $aClone = clone $a;

        self::assertNotSame($a, $aClone);
        self::assertEquals($a, $aClone);

        self::assertNotSame($a->getChildren(), $aClone->getChildren());
        self::assertEquals($a->getChildren(), $aClone->getChildren());
    }

    /**
     * @covers ::hasParent
     * @covers ::setParent
     */
    public function testHasAndSetParent()
    {
        $a = new A();
        self::assertFalse($a->hasParent());

        $b = new B();
        $a->setParent($b);
        self::assertTrue($a->hasParent());
        self::assertSame($b, $a->getParent());

        $a->setParent($b);
        self::assertSame($b, $a->getParent());

        $c = new C();
        $a->setParent($c);

        self::assertSame($c, $a->getParent());
        self::assertTrue($c->hasChild($a));
        self::assertFalse($b->hasChild($a));
    }

    /**
     * @covers ::getParent
     * @covers ::setParent
     */
    public function testGetAndSetParent()
    {
        $a = new A();
        self::assertNull($a->getParent());

        $a->setParent($b = new B());
        self::assertSame($b, $a->getParent());
    }

    /**
     * @covers ::hasChildren
     */
    public function testHasChildren()
    {
        $a = new A();
        self::assertFalse($a->hasChildren());

        $a->appendChild(new B());
        self::assertTrue($a->hasChildren());
    }

    /**
     * @covers ::append
     * @covers ::prepareChild
     */
    public function testAppend()
    {
        $a = new A();
        $b = new B();
        $c = new C();
        $a->setParent($c);
        $a->append($b);

        self::assertSame($b, $c->getChildAt(1));
        $b->append($a);
        self::assertSame($b, $c->getChildAt(0));
        self::assertSame($a, $c->getChildAt(1));
    }

    /**
     * @covers ::prepend
     */
    public function testPrepend()
    {
        $a = new A();
        $b = new B();
        $c = new C();
        $a->setParent($c);
        $a->prepend($b);

        self::assertSame($b, $c->getChildAt(0));
    }

    /**
     * @covers ::is
     */
    public function testIs()
    {
        $a = new A();

        self::assertTrue($a->is(function ($node) {
            return $node instanceof A;
        }));
        self::assertFalse($a->is(function ($node) {
            return $node instanceof B;
        }));
    }

    /**
     * @covers ::getChildCount
     * @covers ::count
     */
    public function testGetChildCount()
    {
        $a = new A();
        self::assertSame(0, $a->getChildCount());
        self::assertSame(0, $a->count());
        self::assertSame(0, count($a));

        $a->appendChild(new B());
        self::assertSame(1, $a->getChildCount());
        self::assertSame(1, $a->count());
        self::assertSame(1, count($a));

        $a->appendChild(new C());
        self::assertSame(2, $a->getChildCount());
        self::assertSame(2, $a->count());
        self::assertSame(2, count($a));
    }

    /**
     * @covers ::getChildIndex
     * @covers ::getIndex
     */
    public function testGetChildIndex()
    {
        $a = new A(null, [
            $b = new B(),
            $c = new C(),
            $d = new D(),
        ]);

        self::assertSame(0, $a->getChildIndex($b));
        self::assertSame(0, $b->getIndex());
        self::assertSame(1, $a->getChildIndex($c));
        self::assertSame(1, $c->getIndex());
        self::assertSame(2, $a->getChildIndex($d));
        self::assertSame(2, $d->getIndex());
        self::assertSame(null, $a->getIndex());
    }

    /**
     * @covers ::setChildren
     * @covers ::getChildren
     * @covers ::getParent
     */
    public function testGetAndSetChildren()
    {
        $a = new A();
        $a->setChildren([$b = new B(), $c = new C()]);

        self::assertSame($a, $b->getParent());
        self::assertSame($a, $c->getParent());
        self::assertSame([$b, $c], $a->getChildren());
    }

    /**
     * @covers ::removeChildren
     * @covers ::getParent
     */
    public function testRemoveChildren()
    {
        $a = new A(null, [$b = new B(), $c = new C(), $d = new D()]);
        self::assertCount(3, $a);

        $a->removeChildren();
        self::assertNull($b->getParent());
        self::assertNull($c->getParent());
        self::assertNull($d->getParent());
        self::assertCount(0, $a);
    }

    /**
     * @covers ::hasChild
     */
    public function testHasChild()
    {
        $a = new A(null, [
            $b = new B(),
            $c = new C(),
        ]);

        $d = new D();

        self::assertTrue($a->hasChild($b));
        self::assertTrue($a->hasChild($c));
        self::assertFalse($a->hasChild($d));
    }

    /**
     * @covers ::hasChildAt
     */
    public function testHasChildAt()
    {
        $a = new A(null, [
            $b = new B(),
            $c = new C(),
        ]);

        $d = new D();

        self::assertTrue($a->hasChildAt(0));
        self::assertTrue($a->hasChildAt(1));
        self::assertFalse($a->hasChildAt(2));
    }

    /**
     * @covers ::getChildAt
     */
    public function testGetChildAt()
    {
        $a = new A(null, [
            $b = new B(),
            $c = new C(),
        ]);

        self::assertSame($b, $a->getChildAt(0));
        self::assertSame($c, $a->getChildAt(1));
    }

    /**
     * @covers ::getChildAt
     */
    public function testGetChildAtWithInvalidOffset()
    {
        $a = new A();
        self::setExpectedException(AstException::class);
        $a->getChildAt(3);
    }

    /**
     * @covers ::removeChildAt
     */
    public function testRemoveChildAt()
    {
        $a = new A(null, [
            $b = new B(),
            $c = new C(),
        ]);

        self::assertCount(2, $a);

        $a->removeChildAt(0);
        self::assertCount(1, $a);
        self::assertSame($c, $a->getChildAt(0));
    }

    /**
     * @covers ::removeChildAt
     */
    public function testRemoveChildAtWithInvalidOffset()
    {
        $a = new A();
        self::setExpectedException(AstException::class);
        $a->removeChildAt(3);
    }

    /**
     * @covers ::appendChild
     * @covers ::prependChild
     * @covers ::prepareChild
     * @covers ::finishChild
     * @covers ::getIndex
     * @covers ::getChildAt
     */
    public function testAppendAndPrependChild()
    {
        $node = new Node();

        $node->appendChild($a = new A());
        $b = new B($node);
        $node->prependChild($c = new C());
        $node->appendChild($d = new D());

        self::assertSame(1, $a->getIndex());
        self::assertSame(2, $b->getIndex());
        self::assertSame(0, $c->getIndex());
        self::assertSame(3, $d->getIndex());
        self::assertInstanceOf(A::class, $node->getChildAt(1));
        self::assertInstanceOf(B::class, $node->getChildAt(2));
        self::assertInstanceOf(C::class, $node->getChildAt(0));
        self::assertInstanceOf(D::class, $node->getChildAt(3));
    }

    /**
     * @covers ::appendChild
     * @covers ::prependChild
     * @covers ::remove
     * @covers ::removeChild
     * @covers ::getChildren
     */
    public function testRemoveChild()
    {
        $node = new Node();

        $node->appendChild($a = new A());
        $b = new B($node);
        $node->prependChild($c = new C());
        $node->appendChild($d = new D());

        $a->remove();
        $node->removeChild($b);

        self::assertSame([$c, $d], $node->getChildren());
    }

    /**
     * @covers ::appendChild
     * @covers ::prependChild
     * @covers ::remove
     * @covers ::getPreviousSibling
     * @covers ::getNextSibling
     */
    public function testSiblingConnections()
    {
        $node = new Node();

        $node->appendChild($a = new A());
        $b = new B($node);
        $node->prependChild($c = new C());
        $node->appendChild($d = new D());

        $a->remove();

        self::assertSame(null, $c->getPreviousSibling());
        self::assertSame($b, $c->getNextSibling());
        self::assertSame(null, $d->getNextSibling());
        self::assertSame($b, $d->getPreviousSibling());
        self::assertSame($c, $b->getPreviousSibling());
        self::assertSame($d, $b->getNextSibling());
    }

    /**
     * @covers ::appendChild
     * @covers ::findChildrenArray
     * @covers ::getNextSibling
     */
    public function testFindChildren()
    {
        $node = new Node();

        $node->appendChild(new A())
            ->appendChild((new B())->appendChild(new B()))
            ->appendChild(new B())
            ->appendChild(new D())
            ->appendChild((new B())->appendChild((new B())->appendChild(new B())))
            ->appendChild(new C())
            ->appendChild(new D())
            ->appendChild(new C())
            ->appendChild(new D())
            ->appendChild(new C())
            ->appendChild(new D());

        $aChildren = $node->findChildrenArray(function (NodeInterface $node) {
            return $node instanceof A;
        });

        $bDeepChildren = $node->findChildrenArray(function (NodeInterface $node) {
            return $node instanceof B;
        });

        $bFirstChildren = $node->findChildrenArray(function (NodeInterface $node) {
            return $node instanceof B;
        }, 0);

        $bSecondChildren = $node->findChildrenArray(function (NodeInterface $node) {
            return $node instanceof B;
        }, 1);

        $cChildren = $node->findChildrenArray(function (NodeInterface $node) {
            return $node instanceof C;
        });

        $dChildren = $node->findChildrenArray(function (NodeInterface $node) {
            return $node instanceof D;
        });

        self::assertCount(1, $aChildren, 'A children');
        self::assertCount(6, $bDeepChildren, 'B deep children');
        self::assertCount(3, $bFirstChildren, 'B first level');
        self::assertCount(5, $bSecondChildren, 'B 2 levels');
        self::assertCount(3, $cChildren, 'C children');
        self::assertCount(4, $dChildren, 'D children');
    }

    /**
     * @covers                   ::insertBefore
     * @expectedException        \Phug\AstException
     * @expectedExceptionMessage Failed to insert before: Passed child is not a child of element to insert in
     */
    public function testInsertBeforeWithBadSibling()
    {
        $a = new A();
        $a->insertBefore(new C(), new B());
    }

    /**
     * @covers ::insertBefore
     * @covers ::prepareChild
     * @covers ::finishChild
     */
    public function testInsertBefore()
    {
        $a = new A();
        $b = new B();
        $c = new C();
        $d = new D();
        $a->appendChild($b);
        $c->appendChild($d);
        $c->insertBefore($d, $b);

        self::assertSame(2, $c->getChildCount(), 'C children');
        self::assertSame(0, $a->getChildCount(), 'A children');
        self::assertTrue($c->hasChild($b));
        self::assertTrue($c->hasChild($d));
        self::assertSame($b, $c->getChildAt(0));
        self::assertSame($d, $c->getChildAt(1));
    }

    /**
     * @covers                   ::insertAfter
     * @expectedException        \Phug\AstException
     * @expectedExceptionMessage Failed to insert after: Passed child is not a child of element to insert in
     */
    public function testInsertAfterWithBadSibling()
    {
        $a = new A();
        $a->insertAfter(new C(), new B());
    }

    /**
     * @covers ::insertAfter
     * @covers ::prepareChild
     * @covers ::finishChild
     */
    public function testInsertAfter()
    {
        $a = new A();
        $b = new B();
        $c = new C();
        $d = new D();
        $a->appendChild($b);
        $c->appendChild($d);
        $c->insertAfter($d, $b);

        self::assertSame(2, $c->getChildCount(), 'C children');
        self::assertSame(0, $a->getChildCount(), 'A children');
        self::assertTrue($c->hasChild($b));
        self::assertTrue($c->hasChild($d));
        self::assertSame($b, $c->getChildAt(1));
        self::assertSame($d, $c->getChildAt(0));
    }

    /**
     * @covers ::getIterator
     * @covers ::offsetExists
     * @covers ::offsetGet
     * @covers ::offsetSet
     * @covers ::offsetUnset
     */
    public function testGetIterator()
    {
        $a = new A();
        $b = new B();
        $c = new C();
        $d = new D();
        $a->appendChild($b);
        $a->appendChild($c);
        $result = [];
        foreach ($a as $child) {
            $result[] = $child;
        }

        self::assertSame([$b, $c], $result);
        self::assertTrue(isset($a[0]));
        self::assertTrue(isset($a[1]));
        self::assertFalse(isset($a[2]));
        self::assertSame($b, $a[0]);
        self::assertSame($c, $a[1]);

        $a[1] = $d;
        self::assertSame($d, $a[1]);

        unset($a[1]);
        self::assertFalse(isset($a[1]));

        $result = [];
        foreach ($a as $child) {
            $result[] = $child;
        }
        self::assertSame([$b], $result);


        $a[1] = $d;
        $result = [];
        foreach ($a as $child) {
            $result[] = $child;
        }
        self::assertSame([$b, $d], $result);
    }

    /**
     * @covers                   ::offsetSet
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Argument 2 passed to Node->offsetSet needs to be instance of Phug\Ast\NodeInterface
     */
    public function testOffsetSetInvalidArgument()
    {
        $a = new A();
        $a[0] = "foo";
    }
}
//@codingStandardsIgnoreEnd
