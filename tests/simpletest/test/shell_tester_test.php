<?php
// $Id$
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../shell_tester.php');
Mock::generate('SimpleShell');

class TestOfShellTestCase extends ShellTestCase
{
    private $mock_shell = false;
    
    public function getShell()
    {
        return $this->mock_shell;
    }
    
    public function testGenericEquality()
    {
        $this->assertEqual('a', 'a');
        $this->assertNotEqual('a', 'A');
    }
    
    public function testExitCode()
    {
        $this->mock_shell = new MockSimpleShell();
        $this->mock_shell->setReturnValue('execute', 0);
        $this->mock_shell->expectOnce('execute', array('ls'));
        $this->assertTrue($this->execute('ls'));
        $this->assertExitCode(0);
    }
    
    public function testOutput()
    {
        $this->mock_shell = new MockSimpleShell();
        $this->mock_shell->setReturnValue('execute', 0);
        $this->mock_shell->setReturnValue('getOutput', "Line 1\nLine 2\n");
        $this->assertOutput("Line 1\nLine 2\n");
    }
    
    public function testOutputPatterns()
    {
        $this->mock_shell = new MockSimpleShell();
        $this->mock_shell->setReturnValue('execute', 0);
        $this->mock_shell->setReturnValue('getOutput', "Line 1\nLine 2\n");
        $this->assertOutputPattern('/line/i');
        $this->assertNoOutputPattern('/line 2/');
    }
}
