<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class FactoryHelper extends \Codeception\Module
{
    private $faker, $I;

    // HOOK: used after configuration is loaded
    public function _initialize()
    {
        $this->faker = \Faker\Factory::create();
        $this->I = $this->getModule('WPDb');
    }

    public function haveOnePost()
    {
        return $this->I->havePostInDatabase([
            'post_title' => $this->faker->sentence(),
            'post_status' => 'publish'
        ]);
    }
}
