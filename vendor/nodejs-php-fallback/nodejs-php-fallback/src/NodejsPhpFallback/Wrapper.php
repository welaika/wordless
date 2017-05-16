<?php

namespace NodejsPhpFallback;

abstract class Wrapper implements WrapperInterface
{
    protected $path;
    protected $contents;
    protected $node;

    public function __construct($file)
    {
        $key = file_exists($file) ? 'path' : 'contents';
        $this->$key = $file;
        $this->node = new NodejsPhpFallback();
    }

    public function execModuleScript($module, $script, $arguments, $fallback = null)
    {
        if (is_null($fallback)) {
            $fallback = function () {
            };
        }

        return $this->node->execModuleScript($module, $script, $arguments, $fallback);
    }

    public function getPath($defaultName = 'source.tmp')
    {
        if ($this->path) {
            return $this->path;
        }

        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $defaultName;
        file_put_contents($path, $this->contents);

        return $path;
    }

    public function getSource()
    {
        return isset($this->contents)
            ? $this->contents
            : file_get_contents($this->path);
    }

    public function getResult()
    {
        $result = $this->compile();
        if ($result !== false && $result !== null) {
            return $result;
        }

        return $this->fallback();
    }

    public function exec()
    {
        return $this->getResult();
    }

    public function write($path)
    {
        return file_put_contents($path, $this->getResult());
    }

    public function __toString()
    {
        return $this->getResult();
    }
}
