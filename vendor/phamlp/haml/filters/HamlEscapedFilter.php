<?php
/* SVN FILE: $Id: HamlEscapedFilter.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * Escaped Filter for {@link http://haml-lang.com/ Haml} class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright		Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.filters
 */

/**
 * Escaped Filter for {@link http://haml-lang.com/ Haml} class.
 * Escapes the text.
 * Code to be interpolated can be included by wrapping it in #().
 * @package			PHamlP
 * @subpackage	Haml.filters
 */
class HamlEscapedFilter extends HamlBaseFilter {
	/**
	 * Run the filter
	 * @param string text to filter
	 * @return string filtered text
	 */
	public function run($text) {
	  return preg_replace(
	  	HamlParser::MATCH_INTERPOLATION,
	  	'<?php echo htmlspecialchars($text); ?>',
	  	htmlspecialchars($text)
	  ) . "\n";
	}
}
