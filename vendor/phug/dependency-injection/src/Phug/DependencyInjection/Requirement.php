<?php

namespace Phug\DependencyInjection;

use Phug\Util\UnorderedArguments;

class Requirement
{
    /**
     * @var bool
     */
    private $required;

    /**
     * @var Dependency
     */
    private $dependency;

    public function __construct()
    {
        $arguments = new UnorderedArguments(func_get_args());

        $this->setRequired($arguments->optional('boolean') ?: false);

        if ($dependency = $arguments->optional(Dependency::class)) {
            $this->setDependency($dependency);
        }

        $arguments->noMoreDefinedArguments();
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $required
     *
     * @return $this
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return Dependency
     */
    public function getDependency()
    {
        return $this->dependency;
    }

    /**
     * @param Dependency $dependency
     *
     * @return $this
     */
    public function setDependency($dependency)
    {
        $this->dependency = $dependency;

        return $this;
    }
}
