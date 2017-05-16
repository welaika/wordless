<?php

namespace Jade\Compiler;

use Jade\Jade;
use Jade\Lexer\Scanner;
use JsPhpize\JsPhpize;

/**
 * Class Jade Compiler.
 */
class ExpressionCompiler extends MixinVisitor
{
    /**
     * @var JsPhpize
     */
    protected $jsPhpize = null;

    public function getArgumentExpression($arg)
    {
        if ($this->getExpressionLanguage() === Jade::EXP_JS) {
            return $this->getPhpCodeFromJs(array($arg));
        }

        $arg = static::convertVarPath($arg);

        // add dollar if missing
        return preg_match('/^' . static::VARNAME . '(\s*,.+)?$/', $arg)
            ? static::addDollarIfNeeded($arg)
            : $arg;
    }

    protected function getExpressionLanguage()
    {
        $expressionLanguage = $this->getOption('expressionLanguage', 'auto');
        if (is_string($expressionLanguage)) {
            $expressionLanguage = strtolower($expressionLanguage);
            if (substr($expressionLanguage, 0, 3) === 'php') {
                return Jade::EXP_PHP;
            }
            if (substr($expressionLanguage, 0, 2) === 'js' || substr($expressionLanguage, 0, 10) === 'javascript') {
                return Jade::EXP_JS;
            }
        }

        return Jade::EXP_AUTO;
    }

    protected function getPhpCodeFromJs($arguments)
    {
        if (
            preg_match('/^\s*array\s*' . Scanner::PARENTHESES . '\s*$/i', $arguments[0]) ||
            preg_match('/^\(*isset\(\$/i', $arguments[0]) ||
            (
                preg_match('/^\s*array_merge\s*' . Scanner::PARENTHESES . '/i', $arguments[0]) &&
                preg_match('/\s*array\s*' . Scanner::PARENTHESES . '\s*/i', $arguments[0])
            )
        ) {
            return $arguments[0];
        }

        if ($this->jsPhpize === null) {
            $this->jsPhpize = new JsPhpize(array(
                'catchDependencies' => true,
            ));
        }

        return rtrim(trim(call_user_func(array($this->jsPhpize, 'compileCode'), $arguments[0])), ';');
    }

    protected function jsToPhp($method, $arguments)
    {
        $code = $this->getPhpCodeFromJs($arguments);

        return in_array($method, array('handleCodePhp')) ? array($code) : $code;
    }

    public function phpizeExpression($method)
    {
        $arguments = array_slice(func_get_args(), 1);

        switch ($this->getExpressionLanguage()) {
            case Jade::EXP_PHP:
                return $arguments[0];
            case Jade::EXP_JS:
                return $this->jsToPhp($method, $arguments);
        }

        return call_user_func_array(array(get_called_class(), $method), $arguments);
    }
}
