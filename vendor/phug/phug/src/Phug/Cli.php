<?php

namespace Phug;

class Cli
{
    /**
     * The facade class name that the cli application will call the methods on.
     *
     * @var string
     */
    protected $facade;

    /**
     * List of available methods/actions in the cli application.
     *
     * @example
     * [
     *   'myMethod'
     *   'anAlias' => 'myMethod',
     *   'anAction' => function ($facade, $arguments) {
     *     list($value, $factor) = $arguments;
     *
     *     return call_user_func([$facade, 'multiply'], $value, $factor);
     *   },
     * ]
     *
     * @var array
     */
    protected $methods;

    /**
     * Cli application constructor. Needs a facade name and a methods list.
     *
     * @param string $facade
     * @param array  $methods
     */
    public function __construct($facade, array $methods)
    {
        $this->facade = $facade;
        $this->methods = $methods;
    }

    protected function convertToKebabCase($string)
    {
        return preg_replace_callback('/[A-Z]/', function ($match) {
            return '-'.strtolower($match[0]);
        }, $string);
    }

    protected function convertToCamelCase($string)
    {
        return preg_replace_callback('/-([a-z])/', function ($match) {
            return strtoupper($match[1]);
        }, $string);
    }

    protected function execute($facade, $method, $arguments, $outputFile)
    {
        $callable = [$facade, $method];
        $arguments = array_map(function ($argument) {
            return in_array(substr($argument, 0, 1), ['[', '{'])
                ? json_decode($argument, true)
                : $argument;
        }, $arguments);
        if (isset($this->methods[$method])) {
            $method = $this->methods[$method];
            $callable = [$facade, $method];
            if (!is_string($method)) {
                $callable = $method;
                $arguments = [$facade, $arguments];
            }
        }

        $text = call_user_func_array($callable, $arguments);
        if ($outputFile) {
            return file_put_contents($outputFile, $text);
        }

        echo $text;

        return true;
    }

    protected function getNamedArgumentBySpaceDelimiter(array &$arguments, $index, $name)
    {
        if ($arguments[$index] === $name) {
            array_splice($arguments, $index, 1);
            if (isset($arguments[$index])) {
                $value = $arguments[$index];
                array_splice($arguments, $index, 1);

                return $value;
            }
        }

        return false;
    }

    protected function getNamedArgumentByEqualOperator(array &$arguments, $index, $name)
    {
        if (preg_match('/^'.preg_quote($name).'=(.*)$/', $arguments[$index], $match)) {
            array_splice($arguments, $index, 1);

            return $match[1];
        }

        return false;
    }

    protected function getNamedArgument(array &$arguments, array $names)
    {
        foreach ($names as $name) {
            foreach ($arguments as $index => $argument) {
                $value = $this->getNamedArgumentBySpaceDelimiter($arguments, $index, $name) ?:
                    $this->getNamedArgumentByEqualOperator($arguments, $index, $name);
                if ($value) {
                    return $value;
                }
            }
        }

        return false;
    }

    /**
     * Run the CLI applications with arguments list, return true for a success status, false for an error status.
     *
     * @param $arguments
     *
     * @return bool
     */
    public function run($arguments)
    {
        $outputFile = $this->getNamedArgument($arguments, ['--output-file', '-o']);
        $bootstrapFile = $this->getNamedArgument($arguments, ['--bootstrap', '-b']);
        if ($bootstrapFile) {
            include $bootstrapFile;
        }
        list(, $action) = array_pad($arguments, 2, null);
        $arguments = array_slice($arguments, 2);
        $facade = $this->facade;
        $method = $this->convertToCamelCase($action);

        if (!$action) {
            echo "You must provide a method.\n";
            $this->listAvailableMethods();

            return false;
        }

        if (!in_array($method, iterator_to_array($this->getAvailableMethods()))) {
            echo "The method $action is not available as CLI command in the $facade facade.\n";
            $this->listAvailableMethods();

            return false;
        }

        return $this->execute($facade, $method, $arguments, $outputFile);
    }

    /**
     * Yield all available methods.
     *
     * @return \Generator
     */
    public function getAvailableMethods()
    {
        foreach ($this->methods as $method => $action) {
            $method = is_int($method) ? $action : $method;
            if (substr($method, 0, 2) !== '__') {
                yield $method;
            }
        }
    }

    /**
     * Dump the list of available methods as textual output.
     */
    public function listAvailableMethods()
    {
        echo "Available methods are:\n";
        foreach ($this->getAvailableMethods() as $method) {
            $action = $this->convertToKebabCase($method);
            $target = isset($this->methods[$method]) ? $this->methods[$method] : $method;
            $key = array_search($target, $this->methods);
            if (is_int($key)) {
                $key = $this->methods[$key];
            }

            echo ' - '.$action.($key && $key !== $method
                ? ' ('.$this->convertToKebabCase($key).' alias)'
                : ''
            )."\n";
        }
    }
}
