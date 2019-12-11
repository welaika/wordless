<?php

namespace Phug\Partial;

use Phug\PhugException;
use Phug\Renderer;
use Phug\Util\ModuleInterface;
use SplObjectStorage;

trait ExtensionsTrait
{
    /**
     * List of global extensions. Class names that add custom behaviors to the engine.
     *
     * @var array
     */
    private static $extensions = [];

    /**
     * Get options from extensions list and default options.
     *
     * @param array $extensions list of extensions instances of class names
     * @param array $options    optional default options to merge with
     *
     * @return array
     */
    public static function getExtensionsOptions(array $extensions, array $options = [])
    {
        $methods = static::getExtensionsGetters();

        foreach ($extensions as $extensionClassName) {
            if (is_a($extensionClassName, ModuleInterface::class, true)) {
                if (!isset($options['modules'])) {
                    $options['modules'] = [];
                }

                $options['modules'][] = $extensionClassName;

                continue;
            }

            static::extractExtensionOptions($options, $extensionClassName, $methods);
        }

        return $options;
    }

    /**
     * Check if an extension is available globally.
     *
     * @param string $extensionClassName
     *
     * @return bool
     */
    public static function hasExtension($extensionClassName)
    {
        return in_array(static::normalizeExtensionClassName($extensionClassName), static::getExtensionIds());
    }

    /**
     * Add an extension to the Phug facade (will be available in the current renderer instance and next static calls).
     * Throws an exception if the extension is not a valid class name.
     *
     * @param string $extensionClassName
     *
     * @throws PhugException
     */
    public static function addExtension($extensionClassName)
    {
        if (!class_exists($extensionClassName)) {
            throw new PhugException(
                'Invalid '.$extensionClassName.' extension given: '.
                'it must be a class name.'
            );
        }

        if (!static::hasExtension($extensionClassName)) {
            self::$extensions[] = $extensionClassName;

            /* @var Renderer $renderer */
            if ($renderer = self::$renderer) {
                $renderer->setOptionsRecursive(static::getOptions());
            }
        }
    }

    /**
     * Remove an extension from the Phug facade (remove from current renderer instance).
     *
     * @param string $extensionClassName
     */
    public static function removeExtension($extensionClassName)
    {
        if (static::hasExtension($extensionClassName)) {
            if (self::$renderer) {
                self::removeExtensionFromCurrentRenderer($extensionClassName);
                self::$renderer->initCompiler();
            }

            self::$extensions = array_diff(self::$extensions, [$extensionClassName]);
        }
    }

    /**
     * Get extensions list added through the Phug facade.
     *
     * @return array
     */
    public static function getExtensions()
    {
        return self::$extensions;
    }

    /**
     * Get extensions list added through the Phug facade as normalized names.
     *
     * @return array
     */
    public static function getExtensionIds()
    {
        return array_map([static::class, 'normalizeExtensionClassName'], self::$extensions);
    }

    protected static function normalizeExtensionClassName($name)
    {
        return ltrim('\\', strtolower($name));
    }

    protected static function normalizeFilterName($name)
    {
        return str_replace(' ', '-', strtolower($name));
    }

    protected static function normalizeKeywordName($name)
    {
        return str_replace(' ', '-', strtolower($name));
    }

    private static function getExtensionsGetters()
    {
        return [
            'includes'            => 'getIncludes',
            'scanners'            => 'getScanners',
            'token_handlers'      => 'getTokenHandlers',
            'node_compilers'      => 'getCompilers',
            'formats'             => 'getFormats',
            'patterns'            => 'getPatterns',
            'filters'             => 'getFilters',
            'keywords'            => 'getKeywords',
            'element_handlers'    => 'getElementHandlers',
            'php_token_handlers'  => 'getPhpTokenHandlers',
            'assignment_handlers' => 'getAssignmentHandlers',
        ];
    }

    private static function resolveExtension($extensionClassName)
    {
        if (!is_string($extensionClassName)) {
            return $extensionClassName;
        }

        static $cache = [];

        if (!isset($cache[$extensionClassName])) {
            $cache[$extensionClassName] = new $extensionClassName();
        }

        return $cache[$extensionClassName];
    }

    private static function getExtensionMethodResult($extensionClassName, $method)
    {
        static $cache = null;

        $extension = static::resolveExtension($extensionClassName);

        if (is_null($cache)) {
            $cache = new SplObjectStorage();
        }

        if (!isset($cache[$extension])) {
            $cache[$extension] = [];
        }

        $methods = $cache[$extension];

        if (!isset($methods[$method])) {
            $methods[$method] = $extension->$method();
            $cache[$extension] = $methods;
        }

        return $methods[$method];
    }

    private static function removeExtensionFromCurrentRenderer($extensionClassName)
    {
        /* @var Renderer $renderer */
        $renderer = self::$renderer;

        if (is_a($extensionClassName, ModuleInterface::class, true)) {
            $renderer->setOption(
                'modules',
                array_filter($renderer->getOption('modules'), function ($module) use ($extensionClassName) {
                    return $module !== $extensionClassName;
                })
            );

            return;
        }

        foreach (['getOptions', 'getEvents'] as $method) {
            static::removeOptions([], static::getExtensionMethodResult($extensionClassName, $method));
        }
        foreach (static::getExtensionsGetters() as $option => $method) {
            static::removeOptions([$option], static::getExtensionMethodResult($extensionClassName, $method));
        }
        $rendererClassName = self::getRendererClassName();
        $renderer->setOptionsDefaults((new $rendererClassName())->getOptions());
    }

    private static function mergeOptions($options, $values)
    {
        foreach ($values as $key => &$value) {
            if (substr($key, 0, 3) === 'on_') {
                if (!is_array($value) || is_callable($value)) {
                    $value = [$value];
                }

                if (isset($options[$key]) && (!is_array($options[$key]) || is_callable($options[$key]))) {
                    $options[$key] = [$options[$key]];
                }
            }
        }

        return array_merge_recursive($options, $values);
    }

    private static function extractExtensionOptions(&$options, $extensionClassName, $methods)
    {
        foreach (['getOptions', 'getEvents'] as $method) {
            $value = static::getExtensionMethodResult($extensionClassName, $method);

            if (!empty($value)) {
                $options = static::mergeOptions($options, $value);
            }
        }

        foreach ($methods as $option => $method) {
            $value = static::getExtensionMethodResult($extensionClassName, $method);

            if (!empty($value)) {
                $options = static::mergeOptions($options, [$option => $value]);
            }
        }
    }
}
