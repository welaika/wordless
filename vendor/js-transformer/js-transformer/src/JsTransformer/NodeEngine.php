<?php

namespace JsTransformer;

use NodejsPhpFallback\NodejsPhpFallback;

class NodeEngine extends NodejsPhpFallback
{
    protected function getNodeRequirePath()
    {
        $var = 'NODE_PATH=' . static::getNodeModules();

        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'set ' . $var . '; &&'
            : $var;
    }

    protected function shellExec($withNode)
    {
        $prefix = $withNode ? $this->getNodeRequirePath() . ' ' . $this->getNodePath() . ' ' : '';

        return function ($script) use ($prefix) {
            return shell_exec($prefix . $script . ' 2>&1');
        };
    }
}
