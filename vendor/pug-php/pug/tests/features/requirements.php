<?php

use Pug\Pug;

class PugRequirementsSuhosinFailure extends Pug
{
    protected function whiteListNeeded($extension)
    {
        return true;
    }

}

class PugRequirementsSuhosinSuccess extends Pug
{
    protected function whiteListNeeded($extension)
    {
        return false;
    }

}

class JadeRequirementsTest extends PHPUnit_Framework_TestCase
{
    public function testStreamWhiteListed()
    {
        $pug = new PugRequirementsSuhosinFailure();
        $requirements = $pug->requirements();
        $this->assertFalse($requirements['streamWhiteListed'], 'streamWhiteListed requirement should be false with PugRequirementsSuhosinFailure');

        $pug = new PugRequirementsSuhosinSuccess();
        $requirements = $pug->requirements();
        $this->assertTrue($pug->requirements('streamWhiteListed'), 'streamWhiteListed requirement should be true with PugRequirementsSuhosinSuccess');
    }

    public function testCacheFolderExists()
    {
        $pug = new Pug(array(
            'cache' => '/path/that/does/not/exists',
        ));
        $requirements = $pug->requirements();
        $this->assertFalse($requirements['cacheFolderExists'], 'cacheFolderExists requirement should be false with /path/that/does/not/exists');

        $pug = new Pug(array(
            'cache' => sys_get_temp_dir(),
        ));
        $requirements = $pug->requirements();
        $this->assertTrue($pug->requirements('cacheFolderExists'), 'cacheFolderExists requirement should be true with sys_get_temp_dir()');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 19
     */
    public function testRequirementThatDoesNotExist()
    {
        $pug = new Pug();
        $pug->requirements('requirementThatDoesNotExist');
    }
}
