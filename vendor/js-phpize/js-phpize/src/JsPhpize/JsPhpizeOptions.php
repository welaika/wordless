<?php

namespace JsPhpize;

use ArrayAccess;
use JsPhpize\Lexer\Pattern;

class JsPhpizeOptions
{
    /**
     * Flag for an allowed truncated parenthesis.
     *
     * @const int
     */
    const FLAG_TRUNCATED_PARENTHESES = 1;

    /**
     * Prefix for specific constants.
     *
     * @const string
     */
    const CONST_PREFIX = '__JPC_';

    /**
     * Prefix for specific variables.
     *
     * @const string
     */
    const VAR_PREFIX = '__jpv_';

    /**
     * @var int
     */
    protected $flags = 0;

    /**
     * @var array
     */
    protected $patternsCache = array();

    /**
     * Pass options as array or no parameters for all options on default value.
     *
     * @param array|ArrayAccess $options list of options.
     */
    public function __construct($options = array())
    {
        $this->options = is_array($options) || $options instanceof ArrayAccess ? $options : array();
        if (!isset($this->options['patterns'])) {
            $this->options['patterns'] = array(
                new Pattern(10, 'newline', '\n'),
                new Pattern(20, 'comment', '\/\/.*?\n|\/\*[\s\S]*?\*\/'),
                new Pattern(30, 'string', '"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\''),
                new Pattern(40, 'number', '0[bB][01]+|0[oO][0-7]+|0[xX][0-9a-fA-F]+|(\d+(\.\d*)?|\.\d+)([eE]-?\d+)?'),
                new Pattern(50, 'lambda', '=>'),
                new Pattern(60, 'operator', array('delete', 'typeof', 'void'), true),
                new Pattern(65, 'unexpected', array('::')),
                new Pattern(70, 'operator', array('>>>=', '<<=', '>>=', '**=')),
                new Pattern(80, 'operator', array('++', '--', '&&', '||', '**', '>>>', '<<', '>>')),
                new Pattern(90, 'operator', array('===', '!==', '>=', '<=', '<>', '!=', '==', '>', '<')),
                new Pattern(95, 'regexp', '\/(?:\\\\\S|[^\s\/\\\\])*\/[gimuy]*'),
                new Pattern(100, 'operator', '[\\|\\^&%\\/\\*\\+\\-]='),
                new Pattern(110, 'operator', '[\\[\\]\\{\\}\\(\\)\\:\\.\\/\\*~\\!\\^\\|&%\\?,;\\+\\-]'),
                new Pattern(120, 'keyword', array('as', 'async', 'await', 'break', 'case', 'catch', 'class', 'const', 'continue', 'debugger', 'default', 'do', 'else', 'enum', 'export', 'extends', 'finally', 'for', 'from', 'function', 'get', 'if', 'implements', 'import', 'in', 'instanceof', 'interface', 'let', 'new', 'of', 'package', 'private', 'protected', 'public', 'return', 'set', 'static', 'super', 'switch', 'throw', 'try', 'var', 'while', 'with', 'yield', 'yield*'), true),
                new Pattern(130, 'constant', 'null|undefined|Infinity|NaN|true|false|Math\.[A-Z][A-Z0-9_]*' . (isset($this->options['disableConstants']) && $this->options['disableConstants']
                    ? ''
                    : '|[A-Z][A-Z0-9\\\\_\\x7f-\\xff]*|[\\\\\\x7f-\\xff_][A-Z0-9\\\\_\\x7f-\\xff]*[A-Z][A-Z0-9\\\\_\\x7f-\\xff]*'), true),
                new Pattern(135, 'variable', '[a-zA-Z\\\\\\x7f-\\xff\\$_][a-zA-Z0-9\\\\_\\x7f-\\xff\\$]*', '$'),
                new Pattern(140, 'operator', '[\\s\\S]'),
            );
        }
    }

    /**
     * Add a pattern.
     *
     * @param Pattern $pattern
     *
     * @return $this
     */
    public function addPattern(Pattern $pattern)
    {
        $this->clearPatternsCache();

        $this->options['patterns'][] = $pattern;

        return $this;
    }

    /**
     * Remove patterns using a filter function.
     *
     * @param callable $removeFunction
     *
     * @return $this
     */
    public function removePatterns($removeFunction)
    {
        $this->clearPatternsCache();

        $this->options['patterns'] = array_filter($this->options['patterns'], $removeFunction);

        return $this;
    }

    /**
     * Return cached and ordered patterns list.
     *
     * @return array
     */
    public function getPatterns()
    {
        if (!$this->patternsCache) {
            $this->patternsCache = $this->getOption('patterns');
            usort($this->patternsCache, function (Pattern $first, Pattern $second) {
                return $first->priority - $second->priority;
            });
        }

        return $this->patternsCache;
    }

    /**
     * Clear the patterns cache.
     *
     * @return $this
     */
    public function clearPatternsCache()
    {
        $this->patternsCache = null;

        return $this;
    }

    /**
     * Retrieve an option value.
     *
     * @param string $key     option name.
     * @param mixed  $default value to return if the option is not set.
     *
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }

    /**
     * Retrieve the prefix of specific variables.
     *
     * @return string
     */
    public function getVarPrefix()
    {
        return $this->getOption('varPrefix', static::VAR_PREFIX);
    }

    /**
     * Retrieve the prefix of specific variables.
     *
     * @return string
     */
    public function getHelperName($key)
    {
        $helpers = $this->getOption('helpers', array());

        return is_array($helpers) && isset($helpers[$key])
            ? $helpers[$key]
            : $key;
    }

    /**
     * Retrieve the prefix of specific constants.
     *
     * @return string
     */
    public function getConstPrefix()
    {
        return $this->getOption('constPrefix', static::CONST_PREFIX);
    }

    /**
     * @return int
     */
    public function hasFlag($flag)
    {
        return $this->flags & $flag;
    }

    /**
     * @param int  $flag    flag to set
     * @param bool $enabled flag state
     */
    public function setFlag($flag, $enabled)
    {
        if ($enabled) {
            $this->flags |= $flag;

            return;
        }

        $this->flags &= ~$flag;
    }
}
