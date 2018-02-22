<?php

namespace Phug;

use Phug\Partial\ExtensionsTrait;
use Phug\Partial\FacadeOptionsTrait;

class Phug
{
    use ExtensionsTrait;
    use FacadeOptionsTrait;

    /**
     * List of global filters stored as array where keys are filter names, and values the action callback.
     *
     * @var array
     */
    private static $filters = [];

    /**
     * List of global custom keywords stored as array where keys are keyword names, and values the action callback.
     *
     * @var array
     */
    private static $keywords = [];

    /**
     * The current rendering instance used for ::compile(File,Directory), ::render(File,Directory), ::display(File).
     *
     * @var Renderer
     */
    private static $renderer = null;

    /**
     * Rendering class to be used to create the renderer engine.
     *
     * @var Renderer
     */
    private static $rendererClassName = Renderer::class;

    private static function normalizeFilterName($name)
    {
        return str_replace(' ', '-', strtolower($name));
    }

    private static function normalizeKeywordName($name)
    {
        return str_replace(' ', '-', strtolower($name));
    }

    private static function getOptions(array $options = [])
    {
        $extras = static::getFacadeOptions();
        foreach (['filters', 'keywords'] as $option) {
            $method = 'get'.ucfirst($option);
            $extras[$option] = static::$method();
        }

        return array_merge_recursive(self::getExtensionsOptions(self::$extensions, $extras), $options);
    }

    /**
     * Set the engine class used to render templates.
     *
     * @example Phug::setRendererClassName(\Tale\Pug\Renderer::class)
     * @example Phug::setRendererClassName(\Pug\Pug::class)
     *
     * @param string $rendererClassName class name of the custom renderer engine
     */
    public static function setRendererClassName($rendererClassName)
    {
        self::$rendererClassName = $rendererClassName;
    }

    /**
     * Get the current engine class used to render templates.
     *
     * @return string $rendererClassName class name of the custom renderer engine
     */
    public static function getRendererClassName()
    {
        return self::$rendererClassName;
    }

    /**
     * Cleanup previously set options.
     *
     * @example Phug::removeOptions(['filters'], ['coffee' => null]])
     *
     * @param array $path    option base path
     * @param mixed $options options to remove
     */
    public static function removeOptions($path, $options)
    {
        if (self::$renderer && (empty($path) || self::$renderer->hasOption($path))) {
            if (is_array($options)) {
                foreach ($options as $key => $value) {
                    static::removeOptions(array_merge($path, [$key]), $value);
                }

                return;
            }

            self::$renderer->unsetOption($path);
        }
    }

    /**
     * Reset all static options, filters and extensions.
     */
    public static function reset()
    {
        static::resetFacadeOptions();
        self::$renderer = null;
        self::$extensions = [];
        self::$filters = [];
        self::$keywords = [];
    }

    /**
     * Get a renderer with global options and argument options merged.
     *
     * @example Phug::getRenderer([])
     *
     * @param array $options
     *
     * @return Renderer
     */
    public static function getRenderer(array $options = [])
    {
        $options = static::getOptions($options);

        if (!self::$renderer) {
            $rendererClassName = self::getRendererClassName();
            self::$renderer = new $rendererClassName($options);
        } elseif (!empty($options)) {
            self::$renderer->reInitOptions($options);
            self::$renderer->getCompiler()->getFormatter()->initFormats();
        }

        return self::$renderer;
    }

    /**
     * Return a rendered Pug template string.
     *
     * @param string $input      pug source
     * @param array  $parameters variables values
     * @param array  $options    custom options
     *
     * @return string
     */
    public static function render($input, array $parameters = [], array $options = [])
    {
        return static::getRenderer($options)->render($input, $parameters);
    }

    /**
     * Return a rendered Pug file.
     *
     * @param string $path       path to template
     * @param array  $parameters variables values
     * @param array  $options    custom options
     *
     * @return string
     */
    public static function renderFile($path, array $parameters = [], array $options = [])
    {
        return static::getRenderer($options)->renderFile($path, $parameters);
    }

    /**
     * Display a rendered Pug template string. By default, it means HTML output to the buffer.
     *
     * @param string $input      pug source
     * @param array  $parameters variables values
     * @param array  $options    custom options
     */
    public static function display($input, array $parameters = [], array $options = [])
    {
        return static::getRenderer($options)->display($input, $parameters);
    }

    /**
     * Display a rendered Pug file. By default, it means HTML output to the buffer.
     *
     * @param string $path       path to template
     * @param array  $parameters variables values
     * @param array  $options    custom options
     */
    public static function displayFile($path, array $parameters = [], array $options = [])
    {
        return static::getRenderer($options)->displayFile($path, $parameters);
    }

    /**
     * Check if a filter is available globally.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function hasFilter($name)
    {
        return isset(self::$filters[self::normalizeFilterName($name)]);
    }

    /**
     * Get a global filter by name.
     *
     * @param string $name
     *
     * @return callable
     */
    public static function getFilter($name)
    {
        return self::$filters[self::normalizeFilterName($name)];
    }

    /**
     * Set a filter to the Phug facade (will be available in the current renderer instance and next static calls).
     * Throws an exception if the filter is not callable and do not have a parse method.
     *
     * @param string          $name
     * @param callable|string $filter
     *
     * @throws PhugException
     */
    public static function setFilter($name, $filter)
    {
        if (!(
            is_callable($filter) ||
            class_exists($filter) ||
            method_exists($filter, 'parse')
        )) {
            throw new PhugException(
                'Invalid '.$name.' filter given: '.
                'it must be a callable or a class name.'
            );
        }

        self::$filters[self::normalizeFilterName($name)] = $filter;

        if (self::$renderer) {
            self::$renderer->setOptionsRecursive(static::getOptions());
        }
    }

    /**
     * Add a filter. Throws an exception if the name is already taken.
     *
     * @param string          $name
     * @param callable|string $filter
     *
     * @throws PhugException
     */
    public static function addFilter($name, $filter)
    {
        $key = self::normalizeFilterName($name);

        if (isset(self::$filters[$key])) {
            throw new PhugException(
                'Filter '.$name.' is already set.'
            );
        }

        self::setFilter($name, $filter);
    }

    /**
     * Replace a filter. Throws an exception if the name is set.
     *
     * @param string          $name
     * @param callable|string $filter
     *
     * @throws PhugException
     */
    public static function replaceFilter($name, $filter)
    {
        $key = self::normalizeFilterName($name);

        if (!isset(self::$filters[$key])) {
            throw new PhugException(
                'Filter '.$name.' is not set.'
            );
        }

        self::setFilter($name, $filter);
    }

    /**
     * Remove a filter from the Phug facade (remove from current renderer instance).
     *
     * @param string $name
     */
    public static function removeFilter($name)
    {
        $key = self::normalizeFilterName($name);

        if (isset(self::$filters[$key])) {
            unset(self::$filters[$key]);

            if (self::$renderer) {
                self::$renderer->unsetOption(['filters', $key]);
            }
        }
    }

    /**
     * Get filters list added through the Phug facade.
     *
     * @return array
     */
    public static function getFilters()
    {
        return self::$filters;
    }

    /**
     * Check if a keyword is available globally.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function hasKeyword($name)
    {
        return isset(self::$keywords[self::normalizeKeywordName($name)]);
    }

    /**
     * Get a global custom keyword by name.
     *
     * @param string $name
     *
     * @return callable
     */
    public static function getKeyword($name)
    {
        return self::$keywords[self::normalizeKeywordName($name)];
    }

    /**
     * Set a keyword to the Phug facade (will be available in the current renderer instance and next static calls).
     * Throws an exception if the keyword is not callable.
     *
     * @param string          $name
     * @param callable|string $keyword
     *
     * @throws PhugException
     */
    public static function setKeyword($name, $keyword)
    {
        if (!is_callable($keyword)) {
            throw new PhugException(
                'Invalid '.$name.' keyword given: '.
                'it must be a callable or a class name.'
            );
        }

        self::$keywords[self::normalizeKeywordName($name)] = $keyword;

        if (self::$renderer) {
            self::$renderer->setOptionsRecursive(static::getOptions());
        }
    }

    /**
     * Add a keyword. Throws an exception if the name is already taken.
     *
     * @param string          $name
     * @param callable|string $keyword
     *
     * @throws PhugException
     */
    public static function addKeyword($name, $keyword)
    {
        $key = self::normalizeKeywordName($name);

        if (isset(self::$keywords[$key])) {
            throw new PhugException(
                'Keyword '.$name.' is already set.'
            );
        }

        self::setKeyword($name, $keyword);
    }

    /**
     * Replace a keyword. Throws an exception if the name is set.
     *
     * @param string          $name
     * @param callable|string $keyword
     *
     * @throws PhugException
     */
    public static function replaceKeyword($name, $keyword)
    {
        $key = self::normalizeKeywordName($name);

        if (!isset(self::$keywords[$key])) {
            throw new PhugException(
                'Keyword '.$name.' is not set.'
            );
        }

        self::setKeyword($name, $keyword);
    }

    /**
     * Remove a keyword from the Phug facade (remove from current renderer instance).
     *
     * @param string $name
     */
    public static function removeKeyword($name)
    {
        $key = self::normalizeKeywordName($name);

        if (isset(self::$keywords[$key])) {
            unset(self::$keywords[$key]);

            if (self::$renderer) {
                self::$renderer->unsetOption(['keywords', $key]);
            }
        }
    }

    /**
     * Get keywords list added through the Phug facade.
     *
     * @return array
     */
    public static function getKeywords()
    {
        return self::$keywords;
    }

    /**
     * Cache a whole directory and return an array with [$successCount, $errorCount, $errorDetails].
     *
     * @param string      $source      input directories containing pug files
     * @param string|null $destination output for compiled PHP files
     *                                 (if array given, will be used as options array)
     * @param array|null  $options     optional options
     *
     * @throws RendererException
     *
     * @return array
     */
    public static function cacheDirectory($source, $destination = null, $options = null)
    {
        if ($destination && !is_array($destination)) {
            $destination = [
                'cache_dir' => $destination,
            ];
        }

        return static::getRenderer(array_merge(
            $options ?: [], // @codeCoverageIgnore
            $destination ?: [] // @codeCoverageIgnore
        ))->cacheDirectory($source);
    }

    /**
     * Same method as cacheDirectory but with textual human-friendly output.
     *
     * @param string      $source      input directories containing pug files
     * @param string|null $destination output for compiled PHP files
     *                                 (if array given, will be used as options array)
     * @param array|null  $options     optional options
     *
     * @return string
     */
    public static function textualCacheDirectory($source, $destination = null, $options = null)
    {
        list($success, $errors, $errorDetails) = static::cacheDirectory($source, $destination, $options);

        return
            "$success templates cached.\n".
            "$errors templates failed to be cached.\n".
            implode('', array_map(function ($detail) {
                return $detail['inputFile']."\n".
                    $detail['error']->getMessage()."\n".
                    $detail['error']->getTraceAsString();
            }, $errorDetails));
    }

    /**
     * All dynamic methods from the renderer can be called statically with Phug facade.
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if (!self::$renderer && static::isOptionMethod($name)) {
            return static::callOption($name, $arguments);
        }

        return call_user_func_array([static::getRenderer(), $name], $arguments);
    }
}
