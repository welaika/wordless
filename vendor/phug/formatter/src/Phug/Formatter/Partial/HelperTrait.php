<?php

namespace Phug\Formatter\Partial;

trait HelperTrait
{
    protected function helperName($name)
    {
        return static::class.'::'.$name;
    }

    /**
     * @param $name
     * @param $provider
     *
     * @return $this
     */
    public function provideHelper($name, $provider)
    {
        if (is_array($provider)) {
            $callback = array_pop($provider);
            $provider = array_map([$this, 'helperName'], $provider);
            $provider[] = $callback;
        }

        $this->formatter->getDependencies()->provider(
            $this->helperName($name),
            $provider
        );

        return $this;
    }

    /**
     * @param $name
     * @param $provider
     *
     * @return $this
     */
    public function registerHelper($name, $provider)
    {
        $this->formatter->getDependencies()->register(
            $this->helperName($name),
            $provider
        );

        return $this;
    }

    /**
     * @param $name
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    public function helperMethod($name, $method, $args)
    {
        $args[0] = $this->helperName($name);
        $dependencies = $this->formatter->getDependencies();

        return call_user_func_array([$dependencies, $method], $args);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasHelper($name)
    {
        return $this->helperMethod($name, 'has', func_get_args());
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getHelper($name)
    {
        return $this->helperMethod($name, 'get', func_get_args());
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function callHelper($name)
    {
        return $this->helperMethod($name, 'call', func_get_args());
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function requireHelper($name)
    {
        $this->formatter->getDependencies()->setAsRequired(
            $this->helperName($name)
        );

        return $this;
    }
}
