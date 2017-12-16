<?php

namespace JsTransformer;

class JsTransformer
{
    /**
     * @var NodeEngine
     */
    private $nodeEngine;

    public function __construct()
    {
        $this->nodeEngine = new NodeEngine();
    }

    /**
     * @return NodeEngine
     */
    public function getNodeEngine()
    {
        return $this->nodeEngine;
    }

    public function isInstalled($package)
    {
        return NodeEngine::isInstalledPackage($package);
    }

    public function call($package, $arguments = array())
    {
        if (!$this->isInstalled($package)) {
            throw new \RuntimeException($package . ' seems to not be installed.');
        }
        $fallback = function () use ($package) {
            throw new \RuntimeException('node is required to get ' . $package . ' to work.');
        };
        $file = tempnam(sys_get_temp_dir(), 'jst') . '.json';
        file_put_contents($file, json_encode($arguments));
        $script = escapeshellcmd(realpath(__DIR__ . '/../render.js')) . ' ' .
            escapeshellarg($package) . ' ' .
            escapeshellarg($file);

        return $this->nodeEngine->nodeExec($script, $fallback);
    }
}
