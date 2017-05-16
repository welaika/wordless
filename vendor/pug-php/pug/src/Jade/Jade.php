<?php

namespace Jade;

use Jade\Engine\PugJsEngine;
use Jade\Lexer\Scanner;

/**
 * Class Jade\Jade.
 */
class Jade extends PugJsEngine
{
    /**
     * expressionLanguage option values.
     */
    const EXP_AUTO = 0;
    const EXP_JS = 1;
    const EXP_PHP = 2;

    /**
     * @var string
     */
    protected $streamName = 'jade';

    /**
     * Built-in filters.
     *
     * @var array
     */
    protected $filters = array(
        'php'        => 'Jade\Filter\Php',
        'css'        => 'Jade\Filter\Css',
        'cdata'      => 'Jade\Filter\Cdata',
        'escaped'    => 'Jade\Filter\Escaped',
        'javascript' => 'Jade\Filter\Javascript',
    );

    /**
     * @var array
     */
    protected $sharedVariables = array();

    /**
     * Merge local options with constructor $options.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if (is_null($this->options['stream'])) {
            $this->options['stream'] = $this->streamName . '.stream';
        }
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Returns true if suhosin extension is loaded and the stream name
     * is missing in the executor include whitelist.
     * Returns false in any other case.
     *
     * @param string $extension PHP extension name
     *
     * @return bool
     */
    protected function whiteListNeeded($extension)
    {
        return extension_loaded($extension) &&
            false === strpos(
                ini_get($extension . '.executor.include.whitelist'),
                $this->options['stream']
            );
    }

    /**
     * Returns list of requirements in an array identified by keys.
     * For each of them, the value can be true if the requirement is
     * fulfilled, false else.
     *
     * If a requirement name is specified, returns only the matching
     * boolean value for this requirement.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return array|bool
     */
    public function requirements($name = null)
    {
        $requirements = array(
            'streamWhiteListed' => !$this->whiteListNeeded('suhosin'),
            'cacheFolderExists' => !$this->options['cache'] || is_dir($this->options['cache']),
            'cacheFolderIsWritable' => !$this->options['cache'] || is_writable($this->options['cache']),
        );

        if ($name) {
            if (!isset($requirements[$name])) {
                throw new \InvalidArgumentException($name . ' is not in the requirements list (' . implode(', ', array_keys($requirements)) . ')', 19);
            }

            return $requirements[$name];
        }

        return $requirements;
    }

    /**
     * Compile PHP code from a Pug input or a Pug file.
     *
     * @param string $input    input file (or input content if filenname is specified or no file found)
     * @param string $filename filename for the input code
     *
     * @throws \Exception
     *
     * @return string
     */
    public function compile($input, $filename = null)
    {
        $parser = new Parser($input, $filename, $this->options);
        $compiler = new Compiler($this, $this->filters, $parser->getFilename());
        $php = $compiler->compile($parser->parse());
        if (version_compare(PHP_VERSION, '7.0.0') < 0 || $this->getOption('php5compatibility')) {
            $php = preg_replace_callback('/(' . preg_quote('\\Jade\\Compiler::getPropertyFromAnything', '/') . Scanner::PARENTHESES . ')[ \t]*' . Scanner::PARENTHESES . '/', function ($match) {
                $parenthesis = trim(substr($match[3], 1, -1));
                $arguments = $match[1];
                if ($parenthesis !== '') {
                    $arguments .= ', ' . $parenthesis;
                }

                return 'call_user_func(' . $arguments . ')';
            }, $php);
        }
        $postRender = $this->getOption('postRender');
        if (is_callable($postRender)) {
            $php = call_user_func($postRender, $php);
        }

        return $php;
    }

    /**
     * Render using the PHP engine.
     *
     * @param string $input    pug input or file
     * @param string $filename optional file path
     * @param array  $vars     to pass to the view
     *
     * @throws \Exception
     *
     * @return string
     */
    public function renderWithPhp($input, $filename, array $vars)
    {
        if (is_array($filename) || is_object($filename)) {
            $vars = $filename;
            $filename = null;
        }
        $file = $this->options['cache']
            ? $this->cache($input)
            : $this->stream($this->compile($input, $filename));

        extract(array_merge($this->sharedVariables, $vars));
        ob_start();
        try {
            include $file;
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }

    /**
     * Compile HTML code from a Pug input or a Pug file.
     *
     * @param string $input    pug input or file
     * @param string $filename optional file path
     * @param array  $vars     to pass to the view
     *
     * @throws \Exception
     *
     * @return string
     */
    public function render($input, $filename = null, array $vars = array())
    {
        $callback = array($this, 'renderWithPhp');
        $fallback = function () use ($callback, $input, $filename, $vars) {
            return call_user_func($callback, $input, $filename, $vars);
        };

        if ($this->options['pugjs']) {
            return $this->renderWithJs($input, $filename, $vars, $fallback);
        }

        return call_user_func($fallback);
    }

    /**
     * Create a stream wrapper to allow
     * the possibility to add $scope variables.
     *
     * @param string $input
     *
     * @throws \ErrorException
     *
     * @return string
     */
    public function stream($input)
    {
        if ($this->whiteListNeeded('suhosin')) {
            throw new \ErrorException('To run Pug.php on the fly, add "' . $this->options['stream'] . '" to the "suhosin.executor.include.whitelist" settings in your php.ini file.', 4);
        }

        if (!in_array($this->options['stream'], stream_get_wrappers())) {
            stream_wrapper_register($this->options['stream'], 'Jade\\Stream\\Template');
        }

        return $this->options['stream'] . '://data;' . $input;
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
