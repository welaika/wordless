<?php

namespace Phug;

use JsTransformer\JsTransformer;

class JsTransformerFilter
{
    /**
     * @var string
     */
    private $transformerName;

    /**
     * @var JsTransformer
     */
    private $transformer;

    /**
     * JsTransformerFilter constructor.
     *
     * @param JsTransformer $transformer
     * @param string        $transformerName
     */
    public function __construct($transformer, $transformerName)
    {
        $this->transformerName = $transformerName;
        $this->transformer = $transformer;
    }

    /**
     * @return string
     */
    public function getTransformerName()
    {
        return $this->transformerName;
    }

    /**
     * @return JsTransformer
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

    public function __invoke()
    {
        return $this->getTransformer()->call($this->getTransformerName(), func_get_args());
    }
}
