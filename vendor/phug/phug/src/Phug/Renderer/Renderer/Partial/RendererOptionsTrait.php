<?php

namespace Phug\Renderer\Partial;

use Phug\Compiler;
use Phug\CompilerInterface;
use Phug\CompilerModuleInterface;
use Phug\FormatterModuleInterface;
use Phug\LexerModuleInterface;
use Phug\ParserModuleInterface;
use Phug\Renderer\Adapter\EvalAdapter;
use Phug\Renderer\Adapter\FileAdapter;
use Phug\RendererEvent;
use Phug\RendererException;
use Phug\Util\ModuleContainerInterface;
use Phug\Util\OptionInterface;

/**
 * Trait RendererOptionsTrait: require ModuleContainerInterface to be implemented.
 */
trait RendererOptionsTrait
{
    use FileSystemTrait;

    /**
     * @var array
     */
    private $optionEvents = [];

    protected function getDefaultOptions($options)
    {
        return [
            'debug'                 => true,
            'exit_on_error'         => true,
            'enable_profiler'       => false,
            'up_to_date_check'      => true,
            'keep_base_name'        => false,
            'error_reporting'       => null,
            'error_handler'         => null,
            'html_error'            => php_sapi_name() !== 'cli',
            'color_support'         => null,
            'error_context_lines'   => 7,
            'adapter_class_name'    => isset($options['cache_dir']) && $options['cache_dir']
                ? FileAdapter::class
                : EvalAdapter::class,
            'shared_variables'    => [],
            'globals'             => [],
            'modules'             => [],
            'compiler_class_name' => Compiler::class,
            'self'                => false,
            'on_render'           => null,
            'on_html'             => null,
            'filters'             => [
                'cdata' => function ($contents) {
                    return '<![CDATA['.trim($contents).']]>';
                },
            ],
            'macros'              => [],
        ];
    }

    protected function enableModule($moduleClassName, $className, ModuleContainerInterface $container, $optionName)
    {
        /* @var ModuleContainerInterface $this */

        if (in_array($className, class_implements($moduleClassName)) &&
            !$container->hasModule($moduleClassName)
        ) {
            $container->addModule($moduleClassName);
            $this->setOptionsRecursive([
                $optionName => [$moduleClassName],
            ]);
        }
    }

    private function enableModules()
    {
        /* @var ModuleContainerInterface $this */

        $this->addModules($this->getOption('modules'));
        foreach ($this->getStaticModules() as $moduleClassName) {
            $this->enableModule(
                $moduleClassName,
                CompilerModuleInterface::class,
                $this->compiler,
                'compiler_modules'
            );
            $this->enableModule(
                $moduleClassName,
                FormatterModuleInterface::class,
                $this->compiler->getFormatter(),
                'formatter_modules'
            );
            $this->enableModule(
                $moduleClassName,
                ParserModuleInterface::class,
                $this->compiler->getParser(),
                'parser_modules'
            );
            $this->enableModule(
                $moduleClassName,
                LexerModuleInterface::class,
                $this->compiler->getParser()->getLexer(),
                'lexer_modules'
            );
        }
    }

    private function handleOptionAliases()
    {
        /** @var OptionInterface $this */
        if ($this->hasOption('basedir')) {
            $basedir = $this->getOption('basedir');
            $this->setOption('paths', array_merge(
                $this->hasOption('paths')
                    ? (array) $this->getOption('paths')
                    : [],
                is_array($basedir)
                    ? $basedir
                    : [$basedir]
            ));
        }
    }

    /**
     * Initialize/re-initialize the compiler. You should use it if you change initial options (for example: on_render
     * or on_html events, or the compiler_class_name).
     *
     * @throws RendererException
     */
    public function initCompiler()
    {
        $onRender = $this->synchronizeEvent(RendererEvent::RENDER, 'on_render');
        $onHtml = $this->synchronizeEvent(RendererEvent::HTML, 'on_html');

        $this->optionEvents = [
            'on_render' => $onRender,
            'on_html'   => $onHtml,
        ];

        $this->handleOptionAliases();

        $compilerClassName = $this->getOption('compiler_class_name');

        if (!is_a($compilerClassName, CompilerInterface::class, true)) {
            throw new RendererException(
                "Passed compiler class $compilerClassName is ".
                'not a valid '.CompilerInterface::class
            );
        }

        $this->createCompiler($compilerClassName);
    }

    private function synchronizeEvent($event, $key)
    {
        /** @var ModuleContainerInterface $this */
        $callback = $this->getOption($key);

        if ($callback) {
            if (isset($this->optionEvents[$key])) {
                $this->detach($event, $this->optionEvents[$key]);
            }

            $this->attach($event, $callback);
        }

        return $callback;
    }

    private function createCompiler($compilerClassName)
    {
        $this->compiler = new $compilerClassName($this->getOptions());
        $this->initAdapterLinkToCompiler();
    }
}
