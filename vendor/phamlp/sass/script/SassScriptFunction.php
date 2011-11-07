<?php
/* SVN FILE: $Id: SassScriptFunction.php 118 2010-09-21 09:45:11Z chris.l.yates@gmail.com $ */
/**
 * SassScriptFunction class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.script
 */

/**
 * SassScriptFunction class.
 * Preforms a SassScript function.
 * @package			PHamlP
 * @subpackage	Sass.script
 */
class SassScriptFunction {
	/**@#+
	 * Regexes for matching and extracting functions and arguments
	 */
	const MATCH = '/^(((-\w)|(\w))[-\w]*)\(/';
	const MATCH_FUNC = '/^((?:(?:-\w)|(?:\w))[-\w]*)\((.*)\)/';
	const SPLIT_ARGS = '/\s*((?:[\'"].*?["\'])|(?:.+?(?:\(.*\).*?)?))\s*(?:,|$)/';
	const NAME = 1;
	const ARGS = 2;

	private $name;
	private $args;

	/**
	 * SassScriptFunction constructor
	 * @param string name of the function
	 * @param array arguments for the function
	 * @return SassScriptFunction
	 */
	public function __construct($name, $args) {
		$this->name = $name;
		$this->args = $args;
	}

	/**
	 * Evaluates the function.
	 * Look for a user defined function first - this allows users to override
	 * pre-defined functions, then try the pre-defined functions.
	 * @return Function the value of this Function
	 * @throws SassScriptFunctionException if function is undefined
	 */
	public function perform() {
		$name = str_replace('-', '_', $this->name);
		foreach (SassScriptParser::$context->node->parser->function_paths as $path) {	
			$_path = explode(DIRECTORY_SEPARATOR, $path);
			$_class = ucfirst($_path[sizeof($_path) - 2]);
			foreach (array_slice(scandir($path), 2) as $file) {
				$filename = $path . DIRECTORY_SEPARATOR . $file;
				if (is_file($filename)) {
					require_once($filename);
					$class = 'SassExtentions'.$_class.'Functions'. ucfirst(substr($file, 0, -4));
					if (method_exists($class, $name)) {
						return call_user_func_array(array($class, $name), $this->args);
					}
				}
			} // foreach
		} // foreach

		require_once('SassScriptFunctions.php');
		if (method_exists('SassScriptFunctions', $name)) {
			return call_user_func_array(array('SassScriptFunctions', $name), $this->args);
		}
		
		// CSS function: create a SassString that will emit the function into the CSS
		$args = array();
		foreach ($this->args as $arg) {
			$args[] = $arg->toString();
		}
		return new SassString($this->name . '(' . join(', ', $args) . ')');
	}

	/**
	 * Imports files in the specified directory.
	 * @param string path to directory to import
	 * @return array filenames imported
	 */
	private function import($dir) {
		$files = array();

		foreach (array_slice(scandir($dir), 2) as $file) {
			if (is_file($dir . DIRECTORY_SEPARATOR . $file)) {
				$files[] = $file;
				require_once($dir . DIRECTORY_SEPARATOR . $file);
			}
		} // foreach
		return $files;
	}

	/**
	 * Returns a value indicating if a token of this type can be matched at
	 * the start of the subject string.
	 * @param string the subject string
	 * @return mixed match at the start of the string or false if no match
	 */
	public static function isa($subject) {
		if (!preg_match(self::MATCH, $subject, $matches))
			return false;
		
		$match = $matches[0];
		$paren = 1;
		$strpos = strlen($match);
		$strlen = strlen($subject);
		
		while($paren && $strpos < $strlen) {
			$c = $subject[$strpos++];
			
			$match .= $c;
			if ($c === '(') {
				$paren += 1;
			}
			elseif ($c === ')') {
				$paren -= 1;
			}			
		}
		return $match;
	}
	
	public static function extractArgs($string) {
		$args = array();
		$arg = '';
		$paren = 0;
		$strpos = 0;
		$strlen = strlen($string);
		
		while ($strpos < $strlen) {
			$c = $string[$strpos++];
			
			switch ($c) {
				case '(':
					$paren += 1;
					$arg .= $c;
					break;
				case ')':
					$paren -= 1;
					$arg .= $c;
					break;
				case '"':
				case "'":
					$arg .= $c;
					do {
						$_c = $string[$strpos++];
						$arg .= $_c;
					} while ($_c !== $c);
					break;
				case ',':
					if ($paren) {
						$arg .= $c;
						break;
					}
					$args[] = trim($arg);
					$arg = '';
					break;
				default:
					$arg .= $c;
					break;
			}
		}
		
		if ($arg) $args[] = trim($arg);
		return $args;
	}
}
