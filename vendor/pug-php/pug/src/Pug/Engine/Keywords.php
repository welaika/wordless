<?php

namespace Pug\Engine;

use ArrayAccess;
use InvalidArgumentException;
use Phug\ExtensionInterface;
use Phug\Phug;
use Pug\ExtensionContainerInterface;

/**
 * Class Pug\Engine\Keywords.
 */
abstract class Keywords extends Filters
{
    /**
     * @var array
     */
    protected $extensions = [];

    protected function getDefaultOption($name, $defaultValue = null)
    {
        return $this->hasOption($name) ? $this->getOption($name) : $defaultValue;
    }

    protected function hasKeyword($keyword)
    {
        return $this->hasValidCustomKeywordsOption() && $this->getDefaultOption(['keywords', $keyword]);
    }

    protected function hasValidCustomKeywordsOption()
    {
        return is_array($this->getDefaultOption('keywords')) ||
            $this->getDefaultOption('keywords') instanceof ArrayAccess;
    }

    /**
     * Plug an Phug extension to Pug.
     *
     * @param ExtensionInterface $extension
     */
    public function addExtension(ExtensionInterface $extension)
    {
        if (!in_array($extension, $this->extensions)) {
            $this->extensions[] = $extension;
            $options = Phug::getExtensionsOptions([$extension]);
            foreach ([$this, $this->getCompiler()] as $instance) {
                $instance->setOptionsRecursive($options);
            }

            $this->initCompiler();
        }
    }

    /**
     * Set custom keyword.
     *
     * @param string   $keyword the keyword to be found.
     * @param callable $action  action to be executed when the keyword is found.
     */
    public function setKeyword($keyword, $action)
    {
        if (!is_callable($action)) {
            throw new InvalidArgumentException("Please add a callable action for your keyword $keyword", 30);
        }

        if (!$this->hasValidCustomKeywordsOption()) {
            $this->setOption('keywords', []);
        }

        if ($action instanceof ExtensionContainerInterface) {
            $this->addExtension($action->getExtension());
        }

        $this->setOption(['keywords', $keyword], $action);
    }

    /**
     * Add custom keyword.
     *
     * @param string   $keyword the keyword to be found.
     * @param callable $action  action to be executed when the keyword is found.
     */
    public function addKeyword($keyword, $action)
    {
        if ($this->hasKeyword($keyword)) {
            throw new InvalidArgumentException("The keyword $keyword is already set.", 31);
        }

        $this->setKeyword($keyword, $action);
    }

    /**
     * Replace custom keyword.
     *
     * @param string   $keyword the keyword to be found.
     * @param callable $action  action to be executed when the keyword is found.
     */
    public function replaceKeyword($keyword, $action)
    {
        if (!$this->hasKeyword($keyword)) {
            throw new InvalidArgumentException("The keyword $keyword is not set.", 32);
        }

        $this->setKeyword($keyword, $action);
    }

    /**
     * Remove custom keyword.
     *
     * @param string $keyword the keyword to be removed.
     */
    public function removeKeyword($keyword)
    {
        if ($this->hasKeyword($keyword)) {
            $this->unsetOption(['keywords', $keyword]);
        }
    }
}
