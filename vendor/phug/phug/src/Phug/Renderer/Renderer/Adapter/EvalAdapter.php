<?php

namespace Phug\Renderer\Adapter;

use Phug\Formatter\Util\PhpUnwrapString;
use Phug\Renderer\AbstractAdapter;

/**
 * Renderer using `eval()` PHP language construct.
 *
 * Note: this is not more risky than other evaluation ways. Every adapter will execute the code of the Pug
 * templates (inside code statements and expressions). To be safe, you should not render uncontrolled templates
 * unless your environment is a restricted sandbox.
 */
class EvalAdapter extends AbstractAdapter
{
    public function display($__pug_php, array $__pug_parameters)
    {
        $this->execute(function () use ($__pug_php, &$__pug_parameters) {
            extract($__pug_parameters);
            eval(PhpUnwrapString::withoutOpenTag($__pug_php));
        }, $__pug_parameters);
    }
}
