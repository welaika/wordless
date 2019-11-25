<?php

namespace JsPhpize\Compiler;

trait DependenciesTrait
{
    /**
     * @var string
     */
    protected $varPrefix;

    /**
     * @var string
     */
    protected $constPrefix;

    /**
     * @var array
     */
    protected $helpers = [];

    protected function setPrefixes($varPrefix, $constPrefix)
    {
        $this->varPrefix = $varPrefix;
        $this->constPrefix = $constPrefix;
    }

    protected function requireHelper($helper)
    {
        $this->helpers[$helper] = true;
    }

    protected function helperWrap($helper, $arguments)
    {
        $this->requireHelper($helper);

        if (isset($arguments[0]) && preg_match('/^\$.*[^)]$/', $arguments[0])) {
            $helper .= '_with_ref';
        }

        return '$GLOBALS[\'' . $this->varPrefix . $helper . '\'](' .
            implode(', ', $arguments) .
            ')';
    }

    public function getDependencies()
    {
        return array_keys($this->helpers);
    }

    public function compileDependencies($dependencies)
    {
        $php = '';

        foreach ($dependencies as $name) {
            $file = preg_match('/^[a-z0-9_-]+$/i', $name)
                ? __DIR__ . '/Helpers/' . ucfirst($name) . '.h'
                : $name;

            $code = file_exists($file)
                ? file_get_contents($file)
                : $name;

            $php .= '$GLOBALS[\'' . $this->varPrefix . $name . '\'] = ' .
                trim($code) .
                ";\n";

            $refFile = preg_replace('/\.h$/', '.ref.h', $file);

            $code = $refFile !== $file && file_exists($refFile)
                ? file_get_contents($refFile)
                : '$GLOBALS[\'' . $this->varPrefix . $name . '\']';

            $php .= '$GLOBALS[\'' . $this->varPrefix . $name . '_with_ref\'] = ' .
                trim($code) .
                ";\n";
        }

        return $php;
    }
}
