<?php
/* SVN FILE: $Id: SassLiteralExceptions.php 61 2010-04-16 10:19:59Z chris.l.yates $ */
/**
 * Sass literal exception classes.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.script.literals
 */

require_once(dirname(__FILE__).'/../SassScriptParserExceptions.php');

/**
 * Sass literal exception.
 * @package			PHamlP
 * @subpackage	Sass.script.literals
 */
class SassLiteralException extends SassScriptParserException {}

/**
 * SassBooleanException class.
 * @package			PHamlP
 * @subpackage	Sass.script.literals
 */
class SassBooleanException extends SassLiteralException {}

/**
 * SassColourException class.
 * @package			PHamlP
 * @subpackage	Sass.script.literals
 */
class SassColourException extends SassLiteralException {}

/**
 * SassNumberException class.
 * @package			PHamlP
 * @subpackage	Sass.script.literals
 */
class SassNumberException extends SassLiteralException {}

/**
 * SassStringException class.
 * @package			PHamlP
 * @subpackage	Sass.script.literals
 */
class SassStringException extends SassLiteralException {}