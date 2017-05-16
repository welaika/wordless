<?php

namespace Jade;

use Jade\Compiler\CodeHandler;
use Jade\Compiler\Options;
use Jade\Lexer\Scanner;
use Jade\Parser\Exception as ParserException;

/**
 * Class Jade Compiler.
 */
class Compiler extends Options
{
    /**
     * Constants and configuration in Compiler/CompilerConfig.php.
     */

    /**
     * @var
     */
    protected $xml;

    /**
     * @var
     */
    protected $parentIndents;

    /**
     * @var array
     */
    protected $buffer = array();
    /**
     * @var array
     */
    protected $filters = array();

    /**
     * @var Jade
     */
    protected $jade = null;

    /**
     * @var string
     */
    protected $quote;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @param array/Jade $options
     * @param array      $filters
     */
    public function __construct($options = array(), array $filters = array(), $filename = null, $jsPhpize = null)
    {
        $this->options = $this->setOptions($options);
        $this->filters = $filters;
        $this->filename = $filename;
        $this->jsPhpize = $jsPhpize;
    }

    /**
     * Get the filename passed to the compiler.
     *
     * @return Compiler
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get a compiler with the same settings.
     *
     * @return Compiler
     */
    public function subCompiler()
    {
        return new static($this->jade ?: $this->options, $this->filters, $this->filename, $this->jsPhpize);
    }

    /**
     * php closing tag depanding on the pretty print setting.
     *
     * @return string
     */
    protected function closingTag()
    {
        return '?>' . ($this->prettyprint ? ' ' : '');
    }

    /**
     * @param $node
     *
     * @return string
     */
    public function compile($node)
    {
        $this->visit($node);

        $code = ltrim(implode('', $this->buffer));
        if ($this->jsPhpize) {
            $dependencies = $this->jsPhpize->compileDependencies();
            if (!empty($dependencies)) {
                $this->jsPhpize->flushDependencies();
                $code = $this->createCode($dependencies) . $code;
            }
        }

        if ($this->phpSingleLine) {
            // Separate in several lines to get a useable line number in case of an error occurs
            $code = str_replace(array('<?php', '?>'), array("<?php\n", "\n" . $this->closingTag()), $code);
        }

        return $code;
    }

    /**
     * Return code wrapped in PHP tags.
     *
     * @param string code to wrap.
     *
     * @return string
     */
    public function wrapInPhp($code)
    {
        return '<?php ' . $code . ' ' . $this->closingTag();
    }

    /**
     * Return code wrapped out of a PHP code.
     *
     * @param string code to wrap.
     *
     * @return string
     */
    public function wrapOutPhp($code)
    {
        return ' ' . $this->closingTag() . $code . '<?php ';
    }

    /**
     * @param $method
     * @param $arguments
     *
     * @throws \BadMethodCallException If the 'apply' rely on non existing method
     *
     * @return mixed
     */
    protected function apply($method, $arguments)
    {
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException(sprintf('Method %s do not exists', $method), 7);
        }

        return call_user_func_array(array($this, $method), $arguments);
    }

    /**
     * @param      $line
     * @param null $indent
     */
    protected function buffer($line, $indent = null)
    {
        if ($indent === true || ($indent === null && $this->prettyprint)) {
            $line = $this->indent() . $line . $this->newline();
        }

        $this->buffer[] = $line;
    }

    /**
     * @param string $str
     *
     * @return bool|int
     */
    protected function isConstant($str)
    {
        return preg_match('/^' . static::CONSTANT_VALUE . '$/', trim($str));
    }

    protected function handleCodePhp($input, $name = '')
    {
        $handler = new CodeHandler($this, $input, $name);

        return $handler->parse();
    }

    /**
     * @param        $input
     * @param string $name
     *
     * @throws \ErrorException
     *
     * @return array
     */
    public function handleCode($input, $name = '')
    {
        return $this->handleCodePhp($input, $name);
    }

    /**
     * @param $input
     *
     * @throws \ErrorException
     *
     * @return array
     */
    public function handleString($input)
    {
        $result = array();
        $resultsString = array();

        $separators = preg_split(
            '/[+](?!\\()/', // concatenation operator - only js
            $input,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_DELIM_CAPTURE
        );

        foreach ($separators as $part) {
            // $sep[0] - the separator string due to PREG_SPLIT_OFFSET_CAPTURE flag
            // $sep[1] - the offset due to PREG_SPLIT_OFFSET_CAPTURE
            // @todo: = find original usage of this
            //$sep = substr(
            //    $input,
            //    strlen($part[0]) + $part[1] + 1,
            //    isset($separators[$i+1]) ? $separators[$i+1][1] : strlen($input)
            //);

            // @todo: handleCode() in concat
            $part[0] = trim($part[0]);

            if (preg_match('/^(' . Scanner::QUOTED_STRING . ')([\\s\\S]*)$/', $part[0], $match)) {
                if (strlen(trim($match[2]))) {
                    throw new \ErrorException('Unexpected value: ' . $match[2], 8);
                }

                array_push($resultsString, $match[1]);

                continue;
            }

            $code = $this->handleCode($part[0]);

            $result = array_merge($result, array_slice($code, 0, -1));
            array_push($resultsString, array_pop($code));
        }

        array_push($result, implode(' . ', $resultsString));

        return $result;
    }

    /**
     * @param string $text
     *
     * @return mixed
     */
    public function interpolate($text)
    {
        return preg_replace_callback('/(\\\\)?([#!]){(.*?)}/', array($this, 'interpolateFromCapture'), $text);
    }

    /**
     * @param array $match
     *
     * @return string
     */
    protected function interpolateFromCapture($match)
    {
        return $match[1] === ''
            ? trim($this->escapeIfNeeded($match[2] !== '!', $match[3]))
            : substr($match[0], 1);
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function createStatements()
    {
        if (func_num_args() === 0) {
            throw new \InvalidArgumentException('No Arguments provided', 9);
        }

        $arguments = func_get_args();
        $statements = array();
        $variables = array();

        foreach ($arguments as $arg) {
            // skip all if we have a well-formatted variable
            if (preg_match('/^&?\${1,2}_*' . static::VARNAME . '$/', $arg)) {
                array_push($variables, $arg);
                continue;
            }

            $arg = $this->getArgumentExpression($arg);

            // if we have a php constant or variable assume that the string is good php
            if ($this->isConstant($arg) || (
                strpos('{[', substr($arg . ' ', 0, 1)) === false &&
                preg_match('/&?\${1,2}_*' . static::VARNAME . '|[A-Za-z0-9_\\\\]+::/', $arg)
            )) {
                array_push($variables, $arg);
                continue;
            }

            $code = $this->getExpressionLanguage() !== Jade::EXP_JS
                ? $this->handleArgumentValue($arg)
                : array($arg);

            $statements = array_merge($statements, array_slice($code, 0, -1));
            array_push($variables, array_pop($code));
        }

        array_push($statements, $variables);

        return $statements;
    }

    protected function handleArgumentValue($arg)
    {
        if (preg_match('/^' . Scanner::QUOTED_STRING . '/', $arg)) {
            return $this->handleString(trim($arg));
        }

        try {
            return $this->handleCode($arg);
        } catch (\Exception $e) {
            // if a bug occur, try to remove comments
            try {
                return $this->handleCode(preg_replace('#/\\*([\\s\\S]*?)\\*/#', '', $arg));
            } catch (\Exception $e) {
                throw new ParserException('Pug.php did not understand ' . $arg, 10, $e);
            }
        }
    }

    /**
     * @param      $code
     * @param null $statements
     *
     * @return string
     */
    protected function renderPhpStatements($code, $statements = null)
    {
        if ($statements === null) {
            return $code;
        }

        $codeFormat = array_pop($statements);
        array_unshift($codeFormat, $code);

        if (count($statements) === 0) {
            return call_user_func_array('sprintf', $codeFormat);
        }

        $stmtString = '';
        foreach ($statements as $stmt) {
            $stmtString .= $this->newline() . $this->indent() . $stmt . ';';
        }

        $stmtString .= $this->newline() . $this->indent();
        $stmtString .= call_user_func_array('sprintf', $codeFormat);

        return $stmtString . $this->newline() . $this->indent();
    }

    /**
     * @param      $code
     * @param null $statements
     *
     * @return string
     */
    protected function createPhpBlock($code, $statements = null)
    {
        return $this->wrapInPhp($this->renderPhpStatements($code, $statements));
    }

    /**
     * @param $code
     *
     * @return string
     */
    protected function createCode($code)
    {
        return $this->createPhpBlock($code, func_num_args() > 1
            ? $this->apply('createStatements', array_slice(func_get_args(), 1))
            : null
        );
    }
}
