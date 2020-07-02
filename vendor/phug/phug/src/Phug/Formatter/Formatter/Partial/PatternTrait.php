<?php

namespace Phug\Formatter\Partial;

trait PatternTrait
{
    use HelperTrait;

    protected function patternName($name)
    {
        return 'pattern.'.$name;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasPattern($name)
    {
        return $this->hasHelper($this->patternName($name));
    }

    /**
     * @param $name
     * @param $pattern
     *
     * @return PatternTrait
     */
    public function setPattern($name, $pattern)
    {
        if (is_array($pattern)) {
            return $this->provideHelper($this->patternName($name), $pattern);
        }

        $this->registerHelper('patterns.'.$name, $pattern);

        return $this->provideHelper($this->patternName($name), [
            'pattern',
            'patterns.'.$name,
            function ($proceed, $pattern) {
                return function () use ($proceed, $pattern) {
                    $args = func_get_args();
                    array_unshift($args, $pattern);

                    return call_user_func_array($proceed, $args);
                };
            },
        ]);
    }

    /**
     * @param $name
     * @param $pattern
     *
     * @return PatternTrait
     */
    public function addPattern($name, $pattern)
    {
        if (!$this->hasPattern($name)) {
            $this->setPattern($name, $pattern);
        }

        return $this;
    }

    /**
     * @param $patterns
     *
     * @return $this
     */
    public function addPatterns($patterns)
    {
        foreach ($patterns as $name => $pattern) {
            $this->addPattern($name, $pattern);
        }

        return $this;
    }

    /**
     * @param $patterns
     *
     * @return $this
     */
    public function setPatterns($patterns)
    {
        foreach ($patterns as $name => $pattern) {
            $this->setPattern($name, $pattern);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param array  $name
     *
     * @return string
     */
    public function exportHelper($name, array $arguments = null)
    {
        $this->requireHelper($name);

        $code = $this->formatter->getDependencyStorage(
            $this->helperName($name)
        );
        if (!is_null($arguments)) {
            $code .= '('.implode(', ', $arguments).')';
        }

        return $code;
    }
}
