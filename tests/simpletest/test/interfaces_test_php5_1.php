<?php

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
