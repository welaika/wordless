<?php

namespace Phug\Renderer\Adapter;

use Phug\Renderer\AbstractAdapter;

class EvalAdapter extends AbstractAdapter
{
    public function display($__pug_php, array $__pug_parameters)
    {
        extract($__pug_parameters);
        eval('?>'.$__pug_php);
    }
}
