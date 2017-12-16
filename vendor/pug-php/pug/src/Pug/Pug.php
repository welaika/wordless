<?php

namespace Pug;

use InvalidArgumentException;
use Phug\Phug;
use Phug\Renderer\Adapter\StreamAdapter;
use Pug\Engine\Renderer;

/**
 * Class Pug\Pug.
 */
class Pug extends Renderer
{
    /**
     * Pug constructor.
     *
     * @param array|\ArrayAccess $options
     */
    public function __construct($options = [])
    {
        $this->setUpDefaultOptions($options);
        $this->extractExtensionsFromKeywords($options);
        $this->copyNormalizedOptions($options);
        $this->setUpFilterAutoload($options);
        $this->setUpOptionsAliases($options);
        $this->setUpFormats($options);
        $this->setUpCache($options);
        $this->setUpMixins($options);
        $this->setUpEvents($options);
        $this->setUpJsPhpize($options);
        $this->setUpAttributesMapping($options);

        parent::__construct($options);

        $this->initializeJsTransformer();
        $this->initializeLimits();
        $this->initializeJsPhpize();
    }

    /**
     * Set statically the Pug class as the phug default renderer. By default, Phug facade use Phug\Renderer, after
     * you call `Pug::init()`, it will use Pug instead. This will also works for any class that extends Pug.
     */
    public static function init()
    {
        Phug::setRendererClassName(static::class);
    }

    /**
     * This method is kept for backward compatibility but now you should use ->setOption for any option.
     *
     * @deprecated
     * @alias setOption
     *
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function setCustomOption($name, $value)
    {
        return $this->setOption($name, $value);
    }

    /**
     * This method is kept for backward compatibility but now you should use ->setOptions for any option.
     *
     * @deprecated
     * @alias setOptions
     *
     * @param $options
     *
     * @return $this
     */
    public function setCustomOptions($options)
    {
        return $this->setOptions($options);
    }

    /**
     * Returns list of requirements in an array identified by keys.
     * For each of them, the value can be true if the requirement is
     * fulfilled, false else.
     *
     * If a requirement name is specified, returns only the matching
     * boolean value for this requirement.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return array|bool
     */
    public function requirements($name = null)
    {
        $requirements = [
            'cacheFolderExists'     => !$this->getDefaultOption('cache_dir') || is_dir($this->getOption('cache_dir')),
            'cacheFolderIsWritable' => !$this->getDefaultOption('cache_dir') || is_writable($this->getOption('cache_dir')),
        ];

        if ($name) {
            if (!isset($requirements[$name])) {
                throw new InvalidArgumentException($name . ' is not in the requirements list (' . implode(', ', array_keys($requirements)) . ')', 19);
            }

            return $requirements[$name];
        }

        return $requirements;
    }

    /**
     * ->stream() is no longer available, please use Phug\Renderer\Adapter\StreamAdapter instead.
     *
     * @deprecated
     *
     * @throws \ErrorException
     */
    public function stream()
    {
        throw new \ErrorException(
            '->stream() is no longer available, please use ' . StreamAdapter::class . ' instead',
            34
        );
    }
}
