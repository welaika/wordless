<?php

namespace Phug;

use JsTransformer\JsTransformer;

class JsTransformerExtension extends AbstractExtension
{
    /**
     * @var JsTransformer
     */
    private $transformer;

    /**
     * @return JsTransformer
     */
    public function getTransformer()
    {
        if (!$this->transformer) {
            $this->transformer = new JsTransformer();
        }

        return $this->transformer;
    }

    public function getOptions()
    {
        return [
            'filter_resolvers' => [
                'jsTransformer' => function ($name) {
                    $transformer = $this->getTransformer();
                    $package = 'jstransformer-'.$name;

                    return $transformer->isInstalled($package)
                        ? new JsTransformerFilter($transformer, $package)
                        : null;
                },
            ],
        ];
    }
}
