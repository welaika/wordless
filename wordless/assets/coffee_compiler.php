<?php

require_once 'process.php';
require_once 'process_builder.php';

class CoffeeCompiler
{
    private $coffeePath;
    private $nodePath;

    // coffee options
    private $bare;

    public function __construct($coffeePath = '/usr/bin/coffee', $nodePath = '/usr/bin/node')
    {
        $this->coffeePath = $coffeePath;
        $this->nodePath = $nodePath;
    }

    public function setBare($bare)
    {
        $this->bare = $bare;
    }

    public function filter($path, $tempDir)
    {
        $pb = new ProcessBuilder(array(
            $this->nodePath,
            $this->coffeePath,
            '-cp',
        ));

        if ($this->bare) {
            $pb->add('--bare');
        }

        $pb->add($path);
        $proc = $pb->getProcess();
        $code = $proc->run();

        if (0 < $code) {
            throw new \RuntimeException($proc->getErrorOutput());
        }

        return $proc->getOutput();
    }
}
