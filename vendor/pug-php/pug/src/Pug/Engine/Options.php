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

    protected function setUpFilterAutoload(&$options)
    {
        if (!isset($options['filterAutoLoad']) || $options['filterAutoLoad']) {
            if (!isset($options['filter_resolvers'])) {
                $options['filter_resolvers'] = [];
            }

            $options['filter_resolvers']['pugFilterAutoLoad'] = function ($name) {
                if (isset($this->filters[$name])) {
                    return $this->filters[$name];
                }

                foreach (['Pug', 'Jade'] as $namespace) {
                    $filter = $namespace . '\\Filter\\' . implode('', array_map('ucfirst', explode('-', $name)));

                    if (class_exists($filter)) {
                        $this->filters[$name] = method_exists($filter, 'pugInvoke')
                            ? [new $filter(), 'pugInvoke']
                            : (method_exists($filter, 'parse')
                                ? [new $filter(), 'parse']
                                : $filter
                            ); // @codeCoverageIgnore

                        return $this->filters[$name];
                    }
                }
            };
        }
    }

    protected function setUpOptionsAliases(&$options)
    {
        $this->setUpOptionNameHandlers();
        foreach ($this->optionsAliases as $from => $to) {
            if (isset($options[$from]) && !isset($options[$to])) {
                $options[$to] = $options[$from];
            } // @codeCoverageIgnore
        }
    }

    protected function setUpFormats(&$options)
    {
        if (!isset($options['formats']['default'])) {
            $options['formats']['default'] = HtmlFormat::class;
        }
        if (!isset($options['formats']['5'])) {
            $options['formats']['5'] = HtmlFormat::class;
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

    protected function setUpJsPhpize(&$options)
    {
        if (isset($options['jsLanguage'])) {
            if (!isset($options['module_options'])) {
                $options['module_options'] = [];
            }
            $options['module_options']['jsphpize'] = $options['jsLanguage'];
        }
    }

    protected function setUpAttributesMapping(&$options)
    {
        if (isset($options['classAttribute'])) {
            if (!isset($options['attributes_mapping'])) {
                $options['attributes_mapping'] = [];
            }
            $options['attributes_mapping']['class'] = $options['classAttribute'];
        }
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
