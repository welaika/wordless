<?php
/* SVN FILE: $Id$ */
/**
 * PHP Filter for {@link http://haml-lang.com/ Haml} class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright		Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.filters
 */

/**
 * PHP Filter for {@link http://haml-lang.com/ Haml} class.
 * The text will be parsed with the PHP interpreter.
 * @package			PHamlP
 * @subpackage	Haml.filters
 */
class HamlPhpFilter extends HamlBaseFilter {
	/**
	 * Run the filter
	 * @param string text to filter
	 * @return string filtered text
	 */
	public function run($text) {
	  return "<?php\n$text?>\n";
	}
}
