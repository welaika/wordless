<?php

namespace Pug\Engine;

use JsPhpize\JsPhpizePhug;
use Phug\Formatter\Format\HtmlFormat;
use Phug\JsTransformerExtension;
use Phug\Phug;
use Phug\Renderer\Adapter\FileAdapter;
use Pug\ExtensionContainerInterface;

/**
 * Class Pug\Engine\Keywords.
 */
abstract class Options extends OptionsHandler
{
    /**
     * expressionLanguage option values.
     */
    const EXP_AUTO = 0;
    const EXP_JS = 1;
    const EXP_PHP = 2;

    /**
     * Built-in filters.
     *
     * @var array
     */
    protected $filters;

    protected function setUpDefaultOptions(&$options)
    {
        $defaultOptions = [
            'environment' => 'development',
            'formats'     => [],
            'strict'      => false,
        ];
        foreach ($defaultOptions as $key => $value) {
            if (!isset($options[$key])) {
                $options[$key] = $value;
            }
        }
    }

    protected function addFilterResolver(&$options, $name, $filter)
    {
        if (!isset($options['filter_resolvers'])) {
            $options['filter_resolvers'] = [];
        }

        $options['filter_resolvers'][$name] = $filter;
    }

    protected function setUpFilterAutoload(&$options)
    {
        if (!isset($options['filterAutoLoad']) || $options['filterAutoLoad']) {
            $this->addFilterResolver($options, 'pugFilterAutoLoad', function ($name) {
                if (isset($this->filters[$name])) {
                    return $this->filters[$name];
                }

                foreach (['Pug', 'Jade'] as $namespace) {
                    $filter = $namespace . '\\Filter\\' . implode('', array_map('ucfirst', explode('-', $name)));

                    if (class_exists($filter)) {
                        $this->filters[$name] = method_exists($filter, 'pugInvoke')
                            ? [new $filter(), 'pugInvoke']
                            : (
                                method_exists($filter, 'parse')
                                ? [new $filter(), 'parse']
                                : $filter
                            ); // @codeCoverageIgnore

                        return $this->filters[$name];
                    }
                }
            });
        }
    }

    protected function setUpFormats(&$options)
    {
        foreach (['default', '5'] as $format) {
            if (!isset($options['formats'][$format])) {
                $options['formats'][$format] = HtmlFormat::class;
            }
        }
    }

    protected function setUpCache(&$options)
    {
        if (isset($options['cachedir']) && $options['cachedir']) {
            $options['adapterclassname'] = FileAdapter::class;
        }
    }

    protected function setUpMixins(&$options)
    {
        if (isset($options['allowMixinOverride'])) {
            $options['mixin_merge_mode'] = $options['allowMixinOverride']
                ? 'replace'
                : 'ignore';
        }
    }

    protected function setUpEvents(&$options)
    {
        $this->setUpPreRender($options);
        $this->setUpPostRender($options);
    }

    protected function copyDeepOption(&$options, $baseInput, $baseOutput, $outputKey)
    {
        if (isset($options[$baseInput])) {
            if (!isset($options[$baseOutput])) {
                $options[$baseOutput] = [];
            }
            $options[$baseOutput][$outputKey] = $options[$baseInput];
        }
    }

    protected function setUpJsPhpize(&$options)
    {
        $this->copyDeepOption($options, 'jsLanguage', 'module_options', 'jsphpize');
    }

    protected function setUpAttributesMapping(&$options)
    {
        $this->copyDeepOption($options, 'classAttribute', 'attributes_mapping', 'class');
    }

    protected function extractExtensionsFromKeywords(&$options)
    {
        if (isset($options['keywords'])) {
            foreach ($options['keywords'] as $keyword) {
                if ($keyword instanceof ExtensionContainerInterface) {
                    $options = array_merge($options, Phug::getExtensionsOptions([$keyword->getExtension()]));
                }
            }
        }
    }

    protected function initializeLimits()
    {
        $compiler = $this->getCompiler();
        // Options that propagation will fail due to snake_case
        foreach (['memory_limit', 'execution_max_time'] as $option) {
            if (!$compiler->hasOption($option)) {
                $compiler->setOption($option, $this->getOption($option));
            }
        }
    }

    protected function initializeJsPhpize()
    {
        if (strtolower($this->getDefaultOption('expressionLanguage')) !== 'php') {
            $compiler = $this->getCompiler();
            $compiler->addModule(new JsPhpizePhug($compiler));
        }
    }

    protected function initializeJsTransformer()
    {
        $this->addExtension(new JsTransformerExtension());
    }
}
