<?php

namespace Jade\Compiler;

/**
 * Class Jade CompilerConfig.
 * Definitions and low level settings.
 */
abstract class CompilerConfig
{
    /**
     * @const string
     */
    const VARNAME = '[a-zA-Z\\\\\\x7f-\\xff][a-zA-Z0-9\\\\_\\x7f-\\xff]*';

    //  This pattern matches against string constants, some php keywords, number constants and a empty string
    //
    //  the pattern without php escaping:
    //
    //      [ \t]*((['"])(?:\\.|[^'"\\])*\g{-1}|true|false|null|[0-9]+|\b\b)[ \t]*
    //
    //  pattern explained:
    //
    //      [ \t]* - we ignore spaces at the beginning and at the end: useful for the recursive pattern bellow
    //
    //      the first part of the unamed subpattern matches strings:
    //          "(?:\\.|[^"\\])*"  - quoted string with "
    //          '(?:\\.|[^'\\])*'  - quoted string with '
    //
    //      the rest of the pattern:
    //          true|false|null  - language constants
    //          0-9              - number constants
    //          \b\b             - matches a empty string: useful for a empty array
    /**
     * @const string
     */
    const CONSTANT_VALUE = '[ \\t]*("(?:\\\\[\\s\\S]|[^"\\\\])*"|\'(?:\\\\[\\s\\S]|[^\'\\\\])*\'|true|false|null|undefined|[0-9]+|\\b\\b)[ \\t]*';

    /**
     * @const string
     */
    const ESCAPED = 'echo \\Jade\\Compiler::getEscapedValue(%s, %s)';

    /**
     * @const string
     */
    const UNESCAPED = 'echo \\Jade\\Compiler::getUnescapedValue(%s)';

    /**
     * @var array
     */
    protected $doctypes = array(
        '5' => '<!DOCTYPE html>',
        'html' => '<!DOCTYPE html>',
        'default' => '<!DOCTYPE html>',
        'transitional' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
        'strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
        'frameset' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
        '1.1' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
        'basic' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">',
        'mobile' => '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">',
        'xml' => '<<?php echo \'?xml version="1.0" encoding="utf-8" ?\'; ?>>',
    );

    /**
     * @var array
     */
    protected $selfClosing = array('meta', 'img', 'link', 'input', 'source', 'area', 'base', 'col', 'br', 'hr');

    /**
     * @var array
     */
    protected $phpKeywords = array('true', 'false', 'null', 'switch', 'case', 'default', 'endswitch', 'if', 'elseif', 'else', 'endif', 'while', 'endwhile', 'do', 'for', 'endfor', 'foreach', 'endforeach', 'as', 'unless');

    /**
     * @var array
     */
    protected $phpOpenBlock = array('switch', 'if', 'else if', 'elseif', 'else', 'while', 'do', 'foreach', 'for', 'unless');

    /**
     * @var array
     */
    protected $phpCloseBlock = array('endswitch', 'endif', 'endwhile', 'endfor', 'endforeach');
}
