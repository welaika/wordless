<?php

namespace JsPhpize;

use JsPhpize\Compiler\Compiler;
use JsPhpize\Compiler\Exception;
use JsPhpize\Parser\Parser;

class JsPhpize
{
    const CONST_PREFIX = '__JPC_';
    const VAR_PREFIX = '__jpv_';

    /**
     * @var string
     */
    protected $stream = 'jsphpize.stream';

    /**
     * @var bool
     */
    protected $streamsRegistered = array();

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var array
     */
    protected $dependencies = array();

    /**
     * @var array
     */
    protected $sharedVariables = array();

    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    public function getOption($key, $default = null)
    {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }

    public function getVarPrefix()
    {
        return $this->getOption('varPrefix', static::VAR_PREFIX);
    }

    public function getConstPrefix()
    {
        return $this->getOption('constPrefix', static::CONST_PREFIX);
    }

    /**
     * Compile file or code (detect if $input is an exisisting file, else use it as content).
     *
     * @param string $input             file or content
     * @param string $filename          if specified, input is used as content and filename as its name
     * @param bool   $catchDependencies if true, dependencies are not compiled and can be grouped and get separatly
     *
     * @return string
     */
    public function compile($input, $filename = null, $catchDependencies = false)
    {
        if ($filename === null) {
            $filename = file_exists($input) ? $input : null;
            $input = $filename === null ? $input : file_get_contents($filename);
        }
        $parser = new Parser($this, $input, $filename);
        $compiler = new Compiler($this);
        $block = $parser->parse();
        $php = $compiler->compile($block);

        $dependencies = $compiler->getDependencies();
        if ($catchDependencies) {
            $this->dependencies = $dependencies;
            $dependencies = array();
        }
        $php = $compiler->compileDependencies($dependencies) . $php;

        return $php;
    }

    /**
     * Compile a file.
     *
     * @param string $file              input file
     * @param bool   $catchDependencies if true, dependencies are not compiled and can be grouped and get separatly
     *
     * @return string
     */
    public function compileFile($file, $catchDependencies = false)
    {
        return $this->compile(file_get_contents($file), $file, $catchDependencies);
    }

    /**
     * Compile raw code.
     *
     * @param string $code              input code
     * @param bool   $catchDependencies if true, dependencies are not compiled and can be grouped and get separatly
     *
     * @return string
     */
    public function compileCode($code, $catchDependencies = false)
    {
        return $this->compile($code, 'source.js', $catchDependencies);
    }

    /**
     * Compile without the dependencies.
     *
     * @param string $input    file or content
     * @param string $filename if specified, input is used as content and filename as its name
     *
     * @return string
     */
    public function compileWithoutDependencies($input, $filename = null)
    {
        return $this->compile($input, $filename, true);
    }

    /**
     * Return compiled dependencies caught during previous compilations.
     *
     * @return string
     */
    public function compileDependencies()
    {
        $compiler = new Compiler($this);

        return $compiler->compileDependencies($this->dependencies);
    }

    /**
     * Compile and return the code execution result.
     *
     * @param string $input     file or content
     * @param string $filename  if specified, input is used as content and filename as its name
     * @param array  $variables variables to be used in rendered code
     *
     * @return mixed
     */
    public function render($input, $filename = null, array $variables = array())
    {
        if (is_array($filename)) {
            $variables = $filename;
            $filename = null;
        }
        if (!in_array($this->stream, $this->streamsRegistered)) {
            $this->streamsRegistered[] = $this->stream;
            if (in_array($this->stream, stream_get_wrappers())) {
                stream_wrapper_unregister($this->stream);
            }
            $classParts = explode('\\', get_class($this));
            stream_wrapper_register($this->stream, $classParts[0] . '\Stream\ExpressionStream');
        }

        extract(array_merge($this->sharedVariables, $variables));
        try {
            return include $this->stream . '://data;<?php ' . $this->compile($input, $filename);
        } catch (\JsPhpize\Compiler\Exception $e) {
            throw $e;
        } catch (\JsPhpize\Lexer\Exception $e) {
            throw $e;
        } catch (\JsPhpize\Parser\Exception $e) {
            throw $e;
        } catch (\Exception $e) {
            $summary = $input;
            if (strlen($summary) > 50) {
                $summary = substr($summary, 0, 47) . '...';
            }
            throw new Exception("An error occur in [$summary]:\n" . $e->getMessage(), 2, E_ERROR, __FILE__, __LINE__, $e);
        }
    }

    /**
     * Render a file.
     *
     * @param string $file      input file
     * @param array  $variables variables to be used in rendered code
     *
     * @return string
     */
    public function renderFile($file, array $variables = array())
    {
        return $this->render(file_get_contents($file), $file, $variables);
    }

    /**
     * Render raw code.
     *
     * @param string $code      input code
     * @param array  $variables variables to be used in rendered code
     *
     * @return string
     */
    public function renderCode($code, array $variables = array())
    {
        return $this->compile($code, 'source.js', $variables);
    }

    /**
     * Add a variable or an array of variables to be shared with all templates that will be rendered
     * by the instance of Pug.
     *
     * @param array|string $variables|$key an associatives array of variable names and values, or a
     *                                     variable name if you wish to sahre only one
     * @param mixed        $value          if you pass an array as first argument, the second
     *                                     argument will be ignored, else it will used as the
     *                                     variable value for the variable name you passed as first
     *                                     argument
     */
    public function share($variables, $value = null)
    {
        if (!is_array($variables)) {
            $variables = array(strval($variables) => $value);
        }
        $this->sharedVariables = array_merge($this->sharedVariables, $variables);
    }

    /**
     * Remove all previously set shared variables.
     */
    public function resetSharedVariables()
    {
        $this->sharedVariables = array();
    }
}
