<?php

namespace Phug;

use Phug\DependencyInjection\Dependency;

interface DependencyInjectionInterface
{
    public function import($name);

    public function importDependency($name);

    public function isRequired($name);

    public function setAsRequired($name);

    public function getStorageItem($name, $storageVariable);

    public function dumpDependency($name, $storageVariable);

    public function getRequirementsStates();

    public function countRequiredDependencies();

    public function export($storageVariable);

    public function provider($name, $provider);

    public function register($name, $value);

    public function getProvider($name);

    public function get($name);

    public function set($name, Dependency $dependency);

    public function call($name);
}
