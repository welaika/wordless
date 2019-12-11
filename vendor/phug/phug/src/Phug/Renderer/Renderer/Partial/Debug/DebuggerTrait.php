<?php

namespace Phug\Renderer\Partial\Debug;

use Phug\Formatter;
use Phug\Renderer;
use Phug\Renderer\Profiler\EventList;
use Phug\Renderer\Profiler\ProfilerModule;
use Phug\RendererException;
use Phug\Util\Exception\LocatedException;
use Phug\Util\SandBox;
use Phug\Util\SourceLocation;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

trait DebuggerTrait
{
    /**
     * @var string
     */
    private $debugString;

    /**
     * @var string
     */
    private $debugFile;

    /**
     * @var Formatter
     */
    private $debugFormatter;

    private function highlightLine($lineText, $colored, $offset, $options)
    {
        if ($options['html_error']) {
            return '<span class="error-line">'.
                $this->wrapLineWith($lineText, $offset, '<span class="error-offset">%s</span>').
                "</span>\n";
        }

        if (!$colored) {
            return "$lineText\n";
        }

        return "\033[43;30m".
            $this->wrapLineWith($lineText, $offset, "\033[43;31m%s\033[43;30m", 7).
            "\e[0m\n";
    }

    private function wrapLineWith($lineText, $offset, $wrapper, $shift = 0)
    {
        return is_null($offset)
            ? $lineText
            : mb_substr($lineText, 0, $offset + $shift).
            sprintf($wrapper, mb_substr($lineText, $offset + $shift, 1)).
            mb_substr($lineText, $offset + 1 + $shift);
    }

    private function getErrorAsHtml($error, $parameters, $data)
    {
        $sandBox = new SandBox(function () use (
            $error,
            $parameters,
            $data
        ) {
            $dumper = new HtmlDumper();
            $cloner = new VarCloner();
            /* @var \Throwable $error */
            $trace = '## '.$error->getFile().'('.$error->getLine().")\n".$error->getTraceAsString();
            (new Renderer([
                'exit_on_error' => false,
                'debug'         => false,
                'filters'       => [
                    'no-php' => function ($text) {
                        return str_replace('<?', '<<?= "?" ?>', $text);
                    },
                ],
            ]))->displayFile(__DIR__.'/resources/index.pug', array_merge($data, [
                'title'       => $error->getMessage(),
                'trace'       => $trace,
                'parameters'  => $parameters ? $dumper->dump($cloner->cloneVar($parameters), true) : '',
            ]));
        });

        if ($throwable = $sandBox->getThrowable()) {
            return '<pre>'.$throwable->getMessage()."\n\n".$throwable->getTraceAsString().'</pre>';
        }

        return $sandBox->getBuffer();
    }

    private function getErrorMessage($error, SourceLocation $location, $data)
    {
        /* @var \Throwable $error */
        $source = explode("\n", rtrim($data->source));
        $errorType = get_class($error);
        $message = $errorType;
        if ($path = $location->getPath()) {
            $message .= ' in '.$path;
        }
        $line = $location->getLine();
        $offset = $location->getOffset();
        $message .= ":\n".$error->getMessage().' on line '.$line.
            (is_null($offset) ? '' : ', offset '.$offset)."\n\n";
        $contextLines = $data->options['error_context_lines'];
        $code = '';
        $sourceOffset = max(0, $line - 1);
        $untilOffset = isset($source[$sourceOffset]) ? (mb_substr($source[$sourceOffset], 0, $offset ?: 0) ?: '') : '';
        $htmlError = $data->options['html_error'];
        $start = null;
        foreach ($source as $index => $lineText) {
            if (abs($index + 1 - $line) > $contextLines) {
                continue;
            }
            if (is_null($start)) {
                $start = $index + 1;
            }
            $number = strval($index + 1);
            $markLine = $line - 1 === $index;
            if (!$htmlError) {
                $lineText = ($markLine ? '>' : ' ').
                    str_repeat(' ', 4 - mb_strlen($number)).$number.' | '.
                    $lineText;
            }
            if (!$markLine) {
                $code .= $lineText."\n";

                continue;
            }
            $code .= $this->highlightLine($lineText, $data->colored, $offset, $data->options);
            if (!$htmlError && !is_null($offset)) {
                $code .= str_repeat('-', $offset + 7)."^\n";
            }
        }
        if ($htmlError) {
            return $this->getErrorAsHtml($error, $data->parameters, [
                'start'       => $start,
                'untilOffset' => htmlspecialchars($untilOffset),
                'line'        => $line,
                'offset'      => $offset,
                'message'     => trim($message),
                'code'        => $code,
            ]);
        }

        return $message.$code;
    }

    private function getRendererException($error, $code, $line, $offset, $source, $sourcePath, $parameters, $options)
    {
        $colorSupport = $options['color_support'];
        if (is_null($colorSupport)) {
            $colorSupport = $this->hasColorSupport();
        }
        $isPugError = $error instanceof LocatedException;
        /* @var LocatedException $error */
        if ($isPugError) {
            $compiler = $this->getCompiler();
            if ($path = $compiler->locate($error->getLocation()->getPath())) {
                $source = $compiler->getFileContents($path);
            }
        }

        return new RendererException($this->getErrorMessage(
            $error,
            new SourceLocation(
                $isPugError ? $error->getLocation()->getPath() : $sourcePath,
                $isPugError ? $error->getLocation()->getLine() : $line,
                $isPugError ? $error->getLocation()->getOffset() : $offset,
                null
            ),
            (object) [
                'source'     => $source,
                'colored'    => $colorSupport,
                'parameters' => $parameters,
                'options'    => $options,
            ]
        ), $code, $error);
    }

    private function hasColorSupport()
    {
        // @codeCoverageIgnoreStart
        return DIRECTORY_SEPARATOR === '\\'
            ? false !== getenv('ANSICON') ||
            'ON' === getenv('ConEmuANSI') ||
            false !== getenv('BABUN_HOME')
            : (false !== getenv('BABUN_HOME')) ||
            function_exists('posix_isatty') &&
            @posix_isatty(STDOUT);
        // @codeCoverageIgnoreEnd
    }

    private function getDebuggedException($error, $code, $source, $path, $parameters, $options)
    {
        /* @var \Throwable $error */
        $isLocatedError = $error instanceof LocatedException;

        $pugError = $isLocatedError
            ? $error
            : $this->getDebugFormatter()->getDebugError(
                $error,
                $source,
                $path
            );

        if (!($pugError instanceof LocatedException)) {
            return $pugError;
        }

        $line = $pugError->getLocation()->getLine();
        $offset = $pugError->getLocation()->getOffset();
        $sourcePath = $pugError->getLocation()->getPath() ?: $path;
        $compiler = $this->getCompiler();
        $sourceFile = $compiler->locate($sourcePath);

        if ($sourcePath && !$sourceFile) {
            return $error;
        }

        $source = $sourceFile ? $compiler->getFileContents($sourceFile) : $this->debugString;

        return $this->getRendererException($error, $code, $line, $offset, $source, $sourcePath, $parameters, $options);
    }

    /**
     * @param string $debugFile
     */
    protected function setDebugFile($debugFile)
    {
        $this->debugFile = $debugFile;
    }

    /**
     * @param string $debugString
     */
    protected function setDebugString($debugString)
    {
        $this->debugString = $debugString;
    }

    /**
     * @param Formatter $debugFormatter
     */
    protected function setDebugFormatter(Formatter $debugFormatter)
    {
        $this->debugFormatter = $debugFormatter;
    }

    protected function initDebugOptions(Renderer $profilerContainer)
    {
        $profilerContainer->setOptionsDefaults([
            'memory_limit'       => $profilerContainer->getOption('debug') ? 0x3200000 : -1, // 50MB by default in debug
            'execution_max_time' => $profilerContainer->getOption('debug') ? 30000 : -1, // 30s by default in debug
        ]);

        if (!$profilerContainer->getOption('enable_profiler') &&
            (
                $profilerContainer->getOption('execution_max_time') > -1 ||
                $profilerContainer->getOption('memory_limit') > -1
            )
        ) {
            $profilerContainer->setOptionsRecursive([
                'enable_profiler' => true,
                'profiler'        => [
                    'display'        => false,
                    'log'            => false,
                ],
            ]);
        }
        if ($profilerContainer->getOption('enable_profiler')) {
            $profilerContainer->setOptionsDefaults([
                'profiler' => [
                    'time_precision' => 3,
                    'line_height'    => 30,
                    'display'        => true,
                    'log'            => false,
                ],
            ]);
            $events = new EventList();
            $profilerContainer->addModule(new ProfilerModule($events, $profilerContainer));
            $compiler = $profilerContainer->getCompiler();
            $compiler->addModule(new ProfilerModule($events, $compiler));
            $formatter = $compiler->getFormatter();
            $formatter->addModule(new ProfilerModule($events, $formatter));
            $parser = $compiler->getParser();
            $parser->addModule(new ProfilerModule($events, $parser));
            $lexer = $parser->getLexer();
            $lexer->addModule(new ProfilerModule($events, $lexer));
        }
    }

    /**
     * @return Formatter
     */
    public function getDebugFormatter()
    {
        return $this->debugFormatter ?: new Formatter();
    }

    /**
     * Reinitialize debug options then set new options.
     *
     * @param $options
     */
    public function reInitOptions($options)
    {
        /* @var Renderer $this */

        $this->setOptions([
            'memory_limit'       => null,
            'execution_max_time' => null,
            'enable_profiler'    => false,
            'profiler'           => [
                'display'        => false,
                'log'            => false,
            ],
        ]);
        $this->setOptions($options);
        $this->initDebugOptions($this);
    }

    /**
     * Handle error occurred in compiled PHP.
     *
     * @param \Throwable $error
     * @param int        $code
     * @param string     $path
     * @param string     $source
     * @param array      $parameters
     * @param array      $options
     *
     * @throws RendererException
     * @throws \Throwable
     */
    public function handleError($error, $code, $path, $source, $parameters, $options)
    {
        /* @var \Throwable $error */
        $exception = $options['debug']
            ? $this->getDebuggedException($error, $code, $source, $path, $parameters, $options)
            : $error;

        $handler = $options['error_handler'];

        if (!$handler) {
            // @codeCoverageIgnoreStart
            if ($options['debug'] && $options['exit_on_error']) {
                if ($options['html_error']) {
                    echo $exception->getMessage();
                    exit(1);
                }
                if (!function_exists('xdebug_is_enabled') || !xdebug_is_enabled()) {
                    echo $exception->getMessage()."\n".$exception->getTraceAsString();
                    exit(1);
                }
            }
            // @codeCoverageIgnoreEnd

            throw $exception;
        }

        $handler($exception);
    }
}
