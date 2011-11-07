<?php
/* SVN FILE: $Id: SassScriptParserExceptions.php 61 2010-04-16 10:19:59Z chris.l.yates $ */
/**
 * SassScript Parser exception class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.script
 */

require_once(dirname(__FILE__).'/../SassException.php');

/**
 * SassScriptParserException class.
 * @package			PHamlP
 * @subpackage	Sass.script
 */
class SassScriptParserException extends SassException {}

/**
 * SassScriptLexerException class.
 * @package			PHamlP
 * @subpackage	Sass.script
 */
class SassScriptLexerException extends SassScriptParserException {}

/**
 * SassScriptOperationException class.
 * @package			PHamlP
 * @subpackage	Sass.script
 */
class SassScriptOperationException extends SassScriptParserException {}

/**
 * SassScriptFunctionException class.
 * @package			PHamlP
 * @subpackage	Sass.script
 */
class SassScriptFunctionException extends SassScriptParserException {}