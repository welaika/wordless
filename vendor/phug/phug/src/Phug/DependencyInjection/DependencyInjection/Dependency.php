<?php

namespace Phug\DependencyInjection;

use Closure;
use Phug\Util\Partial\NameTrait;
use Phug\Util\Partial\ValueTrait;
use Phug\Util\UnorderedArguments;

class Dependency
{
    use NameTrait;
    use ValueTrait;

    /**
     * @var array
     */
    private $dependencies;

    public function __construct($value)
    {
        if ($value instanceof Closure) {
            $value->bindTo(null);
        }

        $this->setValue($value);

        $arguments = new UnorderedArguments(array_slice(func_get_args(), 1));

        if ($name = $arguments->optional('string')) {
            $this->setName($name);
        }

        $this->setDependencies($arguments->optional('array') ?: []);

        $arguments->noMoreDefinedArguments();
    }

    /**
     * @param array<string> $dependencies
     */
    public function setDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * @return array<string>
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }
}
