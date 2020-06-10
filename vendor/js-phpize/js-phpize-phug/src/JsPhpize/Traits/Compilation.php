<?php

namespace JsPhpize\Traits;

use Exception;
use JsPhpize\Compiler\Exception as CompilerException;
use JsPhpize\JsPhpize;
use JsPhpize\Lexer\Exception as LexerException;
use JsPhpize\Parser\Exception as ParserException;
use Phug\Compiler;
use Phug\CompilerEvent;
use Phug\CompilerInterface;
use Phug\Util\OptionInterface;

trait Compilation
{
    /**
     * @param CompilerInterface $compiler
     *
     * @return JsPhpize
     */
    public function getJsPhpizeEngine(CompilerInterface $compiler)
    {
        if (!$compiler->hasOption('jsphpize_engine')) {
            $compiler->setOption(
                'jsphpize_engine',
                new JsPhpize($this instanceof OptionInterface ? $this->getOptions() : [])
            );
        }

        return $compiler->getOption('jsphpize_engine');
    }

    /**
     * @param JsPhpize $jsPhpize
     * @param int      $code
     * @param string   $fileName
     *
     * @throws Exception
     *
     * @return Exception|string
     */
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
            if ($exception instanceof LexerException ||
                $exception instanceof ParserException ||
                $exception instanceof CompilerException
            ) {
                return $exception;
            }

            throw $exception;
        }
    }

    /**
     * @param CompilerInterface $compiler
     * @param string            $output
     *
     * @return string
     */
    protected function parseOutput($compiler, $output)
    {
        $jsPhpize = $this->getJsPhpizeEngine($compiler);
        $output = preg_replace(
            '/\{\s*\?><\?(?:php)?\s*\}/',
            '{}',
            $output
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

        $jsPhpize->flushDependencies();

        return $output;
    }

    public function handleOutputEvent(Compiler\Event\OutputEvent $event)
    {
        /** @var CompilerInterface $compiler */
        $compiler = $event->getTarget();

        $event->setOutput($this->parseOutput($compiler, $event->getOutput()));

        $compiler->unsetOption('jsphpize_engine');
    }

    /**
     * @return array
     */
    public function getEventListeners()
    {
        return [
            CompilerEvent::OUTPUT => [$this, 'handleOutputEvent'],
        ];
    }
}
