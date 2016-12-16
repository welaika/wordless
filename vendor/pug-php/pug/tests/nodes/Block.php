<?php

use Jade\Nodes\Block;
use Jade\Nodes\Tag;
use Jade\Nodes\Text;

class BlockTest extends PHPUnit_Framework_TestCase
{
    /**
     * Block Node test
     */
    public function testBlock()
    {
        $foo = new Block();
        $bar = new Block(new Tag('small'));
        $this->assertTrue($foo->isEmpty(), 'The block should be empty');
        $bar->replace($foo);
        $this->assertFalse($foo->isEmpty(), 'The block should not be empty');
        $this->assertTrue($foo->nodes[0]->isInline(), 'small tag should be inline');
        $this->assertTrue($foo->nodes[0]->canInline(), 'small tag should can be inline as it does not have children');
    }

    /**
     * Tag Node test
     */
    public function testTag()
    {
        $foo = new Block();
        $foo->push(new Tag('em'));
        $p = new Tag('p');
        $this->assertFalse($p->isInline(), 'p tag should be inline');
        $foo->nodes[0]->block->push(new Tag('small'));
        $this->assertTrue($foo->nodes[0]->canInline(), 'em tag should can be inline as it only contains a small tag');
        $foo->nodes[0]->block->push(new Tag('blockquote'));
        $this->assertFalse($foo->nodes[0]->canInline(), 'em tag should not can be inline as it contains a blockquote tag');
        $foo->nodes[0]->block->push(new Block(new Tag('blockquote')));
        $this->assertFalse($foo->nodes[0]->canInline(), 'em tag should not can be inline as it contains a blockquote tag');
        $foo->push(new Tag('i'));
        $foo->nodes[1]->block->push(new Text('Hello'));
        $this->assertTrue($foo->nodes[1]->canInline(), 'i tag should can be inline as it only contains text');
        $foo->nodes[1]->block->push(new Tag('blockquote'));
        $this->assertFalse($foo->nodes[1]->canInline(), 'i tag should not can be inline if it contains blockquote');
        $foo->nodes[1]->block->nodes[1] = new Tag('small');
        $foo->nodes[1]->block->nodes[1]->block->push(new Tag('small'));
        $this->assertTrue($foo->nodes[1]->canInline(), 'i tag should can be inline with nested block as it only contains text and inline tags');
        $foo->nodes[1]->block->nodes[1]->block->nodes[0] = new Text('Hello');
        $foo->nodes[1]->block->nodes[1]->block->nodes[1] = new Text('Hello');
        $this->assertTrue($foo->nodes[1]->canInline(), 'i tag should can be inline with nested block as it only contains text');
        $foo->nodes[1] = new Tag('div');
        $foo->nodes[1]->block->push(new Tag('i'));
        $foo->nodes[1]->block->push(new Block());
        $this->assertTrue($foo->nodes[1]->canInline(), 'block should not can be inline with nested block if it contains just an i tag');
        $foo->nodes[1]->block->nodes[0] = new Tag('div');
        $this->assertFalse($foo->nodes[1]->canInline(), 'block should not can be inline with nested block if it contains div');
        $foo->nodes[1]->block->nodes[0] = new Tag('i');
        $foo->nodes[1]->block->nodes[1]->push(new Tag('i'));
        $this->assertTrue($foo->nodes[1]->canInline(), 'block should not can be inline with nested block if it contains just i tags');
        $foo->nodes[1]->block->nodes[1]->push(new Tag('p'));
        $this->assertFalse($foo->nodes[1]->canInline(), 'block should not can be inline with nested block if it contains inline and block tags');
        $foo = new Block();
        $foo->nodes[0] = new Tag('b');
        $foo->nodes[0]->block->push(new Text('Hello'));
        $foo->nodes[0]->block->push(new Text('Hello'));
        $this->assertFalse($foo->nodes[0]->canInline(), 'block should not can be inline if it is multi-line');
    }
}
