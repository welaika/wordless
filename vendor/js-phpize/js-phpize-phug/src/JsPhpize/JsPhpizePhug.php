<?php

namespace JsPhpize;

use Exception;
use JsPhpize\Compiler\Exception as CompilerException;
use JsPhpize\Lexer\Exception as LexerException;
use JsPhpize\Parser\Exception as ParserException;
use Phug\AbstractCompilerModule;
use Phug\Compiler;
use Phug\CompilerEvent;
use Phug\CompilerInterface;
use Phug\Renderer;
use Phug\Util\ModuleContainerInterface;

class JsPhpizePhug extends AbstractCompilerModule
{
    public function __construct(ModuleContainerInterface $container)
    {
        parent::__construct($container);

        if ($container instanceof Renderer) {
            return;
        }

        /* @var Compiler $compiler */
        $compiler = $container;

        //Make sure we can retrieve the module options from the container
        $compiler->setOptionsRecursive([
            'module_options' => [
                'jsphpize' => [],
            ],
        ]);

        //Set default options
        $this->setOptionsRecursive([
            'allowTruncatedParentheses' => true,
            'catchDependencies' => true,
            'ignoreDollarVariable' => true,
            'helpers' => [
                'dot' => 'dotWithArrayPrototype',
            ],
        ]);

        //Apply options from container
        $this->setOptionsRecursive($compiler->getOption(['module_options', 'jsphpize']));

        $compiler->setOptionsRecursive([
            'patterns' => [
                'transform_expression' => function ($jsCode) use ($compiler) {
                    $jsPhpize = $this->getJsPhpizeEngine($compiler);
                    $compilation = $this->compile($jsPhpize, $jsCode, $compiler->getPath());

                    if (!($compilation instanceof Exception)) {
                        return $compilation;
                    }

                    return $jsCode;
                },
            ],
        ]);
    }

    /**
     * @return JsPhpize
     */
    public function getJsPhpizeEngine(CompilerInterface $compiler)
    {
        if (!$compiler->hasOption('jsphpize_engine')) {
            $compiler->setOption('jsphpize_engine', new JsPhpize($this->getOptions()));
        }

        return $compiler->getOption('jsphpize_engine');
    }

    public function compile(JsPhpize $jsPhpize, $code, $fileName)
    {
        try {
            $phpCode = trim($jsPhpize->compile($code, $fileName ?: 'raw string'));
            $phpCode = preg_replace('/\{\s*\}$/', '', $phpCode);
            $phpCode = preg_replace(
                '/^(?<!\$)\$+(\$[a-zA-Z\\\\\\x7f-\\xff][a-zA-Z0-9\\\\_\\x7f-\\xff]*\s*[=;])/',
                '$1',
                $phpCode
            );

            return rtrim(trim($phpCode), ';');
        } catch (Exception $exception) {
            if (
                $exception instanceof LexerException ||
                $exception instanceof ParserException ||
                $exception instanceof CompilerException
            ) {
                return $exception;
            }

            throw $exception;
        }
    }

    public function getEventListeners()
    {
        return [
            CompilerEvent::OUTPUT => function (Compiler\Event\OutputEvent $event) {
                $compiler = $event->getTarget();
                $jsPhpize = $this->getJsPhpizeEngine($compiler);
                $output = preg_replace(
                    '/\{\s*\?><\?(?:php)?\s*\}/',
                    '{}',
                    $event->getOutput()
                );
                $output = preg_replace(
                    '/\}\s*\?><\?(?:php)?\s*(' .
                    'else(if)?|for|while|switch|function' .
                    ')(?![a-zA-Z0-9_])/',
                    '} $1',
                    $output
                );

                $dependencies = $jsPhpize->compileDependencies();
                if ($dependencies !== '') {
                    $output = $compiler->getFormatter()->handleCode($dependencies) . $output;
                }

                $event->setOutput($output);

                $jsPhpize->flushDependencies();
                $compiler->unsetOption('jsphpize_engine');
            },
        ];
    }
}
