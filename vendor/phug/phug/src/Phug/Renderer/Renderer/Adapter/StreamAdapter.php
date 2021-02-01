<?php

namespace Phug\Renderer\Adapter;

use Phug\Renderer;
use Phug\Renderer\AbstractAdapter;
use Phug\Renderer\Adapter\Stream\Template;

/**
 * Renderer using pug.stream://data stream.
 *
 * Options to customize the stream ID:
 * - stream_name ("pug" by default)
 * - stream_suffix (".stream" by default)
 */
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
        $stream = $this->getOption('stream_name').$this->getOption('stream_suffix');

        if (!in_array($stream, stream_get_wrappers())) {
            stream_register_wrapper($stream, Template::class);
        }

        $this->renderingFile = $stream.'://data;'.$__pug_php;
    }

    public function display($__pug_php, array $__pug_parameters)
    {
        $this->setRenderingFile($__pug_php);
        $this->execute(function () use ($__pug_php, &$__pug_parameters) {
            extract($__pug_parameters);
            include ${'__pug_adapter'}->getRenderingFile();
        }, $__pug_parameters);
    }

    public function getRenderingFile()
    {
        return $this->renderingFile;
    }
}
