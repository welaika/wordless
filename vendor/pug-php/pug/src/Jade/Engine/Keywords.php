<?php

namespace Jade\Engine;

/**
 * Class Jade\Engine\Keywords.
 */
abstract class Keywords extends Cache
{
    protected function hasKeyword($keyword)
    {
        return $this->hasValidCustomKeywordsOption() && isset($this->options['customKeywords'][$keyword]);
    }

    protected function hasValidCustomKeywordsOption()
    {
        return isset($this->options['customKeywords']) && (
            is_array($this->options['customKeywords']) ||
            $this->options['customKeywords'] instanceof \ArrayAccess
        );
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
            throw new \InvalidArgumentException("Please add a callable action for your keyword $keyword", 30);
        }

        if (!$this->hasValidCustomKeywordsOption()) {
            $this->options['customKeywords'] = array();
        }

        $this->options['customKeywords'][$keyword] = $action;
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
            throw new \InvalidArgumentException("The keyword $keyword is already set.", 31);
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
            throw new \InvalidArgumentException("The keyword $keyword is not set.", 32);
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
            unset($this->options['customKeywords'][$keyword]);
        }
    }
}
