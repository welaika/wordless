<?php
/* SVN FILE: $Id: SassBoolean.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * Compass extension SassScript selectors functions class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.extensions.compass.functions
 */
 
/**
 * Compass extension SassScript selectors functions class.
 * A collection of functions for use in SassSCript.
 * @package			PHamlP
 * @subpackage	Sass.extensions.compass.functions
 */
class SassExtentionsCompassFunctionsSelectors {
	const COMMA_SEPARATOR = '/\s*,\s*/';
	
	private static $defaultDisplay = array(
		'block' => array('address', 'blockquote', 'center', 'dir', 'div', 'dd',
			'dl', 'dt', 'fieldset', 'form', 'frameset h1', 'h2', 'h3', 'h4', 'h5',
			'h6', 'hr', 'isindex', 'menu', 'noframes', 'noscript', 'ol', 'p', 'pre',
			'ul'),
		'inline' => array('a', 'abbr', 'acronym', 'b', 'basefont', 'bdo', 'big',
			'br', 'cite', 'code', 'dfn', 'em', 'font', 'i', 'img', 'input', 'kbd',
			'label', 'q', 's', 'samp', 'select', 'small', 'span', 'strike', 'strong',
			'sub', 'sup', 'textarea', 'tt', 'u', 'var'),
		'table' => array('table'),
		'list-item' => array('li'),
		'table-row-group' => array('tbody'),
		'table-header-group' => array('thead'),
		'table-footer-group' => array('tfoot'),
		'table-row' => array('tr'),
		'table-cell' => array('th', 'td')
	);
	
	# Permute multiple selectors each of which may be comma delimited, the end result is
	# a new selector that is the equivalent of nesting each under the previous selector.
	# To illustrate, the following mixins are equivalent:
	# =mixin-a($selector1, $selector2, $selector3)
	#	 #{$selector1}
	#		 #{$selector2}
	#			 #{$selector3}
	#				 width: 2px
	# =mixin-b($selector1, $selector2, $selector3)
	#	 #{nest($selector, $selector2, $selector3)}
	#		 width: 2px
	public static function nest() {
		if (func_num_args() < 2)
			throw new SassScriptFunctionException('nest() requires two or more arguments', array(), SassScriptParser::$context->node);
			
		$args = func_get_args();
		$arg = array_shift($args);
		$ancestors = preg_split(self::COMMA_SEPARATOR, $arg->value);
		
		foreach ($args as $arg) {
			$nested = array();
			foreach (preg_split(self::COMMA_SEPARATOR, $arg->value) as $descenant) {
				foreach ($ancestors as $ancestor) {
					$nested[] = "$ancestor $descenant"; 
				}
			}
			$ancestors = $nested;		
		}
		sort($nested);
		return new SassString(join(', ', $nested));
	}

	# Permute two selectors, the first may be comma delimited.
	# The end result is a new selector that is the equivalent of nesting the second
	# selector under the first one in a sass file and preceding it with an &.
	# To illustrate, the following mixins are equivalent:
	# =mixin-a($selector, $to_append)
	#	 #{$selector}
	#		 &#{$to_append}
	#			 width: 2px
	# =mixin-b($selector, $to_append)
	#	 #{append_selector($selector, $to_append)}
	#		 width: 2px
	public static function append_selector($selector, $to_append) {
		$appended = array();
		foreach (preg_split(self::COMMA_SEPARATOR, $selector->value) as $ancestor) {
			foreach (preg_split(self::COMMA_SEPARATOR, $to_append->value) as $descendant) {
				$appended[] = $ancestor.$descendant;
			}
		}
		return new SassString(join(', ', $appended));
	}

	# Return the header selectors for the levels indicated
	# Defaults to all headers h1 through h6
	# For example:
	# headers(all) => h1, h2, h3, h4, h5, h6
	# headers(4) => h1, h2, h3, h4
	# headers(2,4) => h2, h3, h4
	public static function headers($from = null, $to = null) {
		if (!$from || ($from instanceof SassString && $from->value === "all")) {
			$from = new SassNumber(1);
			$to = new SassNumber(6);
		}
		elseif ($from && !$to) {
			$to = $from;
			$from = new SassNumber(1);
		}
		
		return new SassString('h' . join(', h', range($from->value, $to->value)));
	}
	
	public static function headings($from = null, $to = null) {
		return self::headers($from, $to);
	}
	
	# Return an enumerated set of comma separated selectors.
	# For example
	# enumerate('foo', 1, 4) => foo-1, foo-2, foo-3, foo-4
	public static function enumerate($prefix, $from, $to, $separator = null) {
		$_prefix = $prefix->value . (!$separator ? '-' : $separator->value);
	  return new SassString($_prefix . join(', '.$_prefix, range($from->value, $to->value)));
	}
	
	# returns a comma delimited string for all the
	# elements according to their default css3 display value.
	public static function elements_of_type($display) {
		return new SassString(join(', ', self::$defaultDisplay[$display->value]));
	}
}
