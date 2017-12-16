<?php

namespace Phug\Partial;

use Phug\PhugException;
use Phug\Renderer;
use Phug\Util\ModuleInterface;

trait ExtensionsTrait
{
    /**
     * List of global extensions. Class names that add custom behaviors to the engine.
     *
     * @var array
     */
    private static $extensions = [];

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

        $extension = new $extensionClassName();
        foreach (['getOptions', 'getEvents'] as $method) {
            static::removeOptions([], $extension->$method());
        }
        foreach (static::getExtensionsGetters() as $option => $method) {
            static::removeOptions([$option], $extension->$method());
        }
        $rendererClassName = self::getRendererClassName();
        $renderer->setOptionsDefaults((new $rendererClassName())->getOptions());
    }

    private static function extractExtensionOptions(&$options, $extensionClassName, $methods)
    {
        $extension = is_string($extensionClassName)
            ? new $extensionClassName()
            : $extensionClassName;
        foreach (['getOptions', 'getEvents'] as $method) {
            $value = $extension->$method();
            if (!empty($value)) {
                $options = array_merge_recursive($options, $value);
            }
        }
        foreach ($methods as $option => $method) {
            $value = $extension->$method();
            if (!empty($value)) {
                $options = array_merge_recursive($options, [$option => $value]);
            }
        }
    }

    protected static function normalizeExtensionClassName($name)
    {
        return ltrim('\\', strtolower($name));
    }

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
        return in_array(
            static::normalizeExtensionClassName($extensionClassName),
            array_map(
                [static::class, 'normalizeExtensionClassName'],
                self::$extensions
            )
        );
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
}
