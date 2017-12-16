<?php

namespace Phug\Renderer\Adapter;

use Phug\Renderer;
use Phug\Renderer\AbstractAdapter;
use Phug\Renderer\Adapter\Stream\Template;

class StreamAdapter extends AbstractAdapter
{
    private $renderingFile;

    public function __construct(Renderer $renderer, $options)
    {
        parent::__construct($renderer, $options);

        $this->setOptionsDefaults([
            'stream_name'   => 'pug',
            'stream_suffix' => '.stream',
        ]);
    }

    protected function setRenderingFile($__pug_php)
    {
        $stream = $this->getOption('stream_name').
            $this->getOption('stream_suffix');
        if (!in_array($stream, stream_get_wrappers())) {
            stream_register_wrapper($stream, Template::class);
        }
        $this->renderingFile = $stream.'://data;'.$__pug_php;
    }

    public function display($__pug_php, array $__pug_parameters)
    {
        $this->setRenderingFile($__pug_php);
        extract($__pug_parameters);
        include $this->getRenderingFile();
    }

    public function getRenderingFile()
    {
        return $this->renderingFile;
    }
}
