<?php
/* SVN FILE: $Id$ */
/**
 * Plain Filter for {@link http://haml-lang.com/ Haml} class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright		Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.filters
 */

/**
 * Plain Filter for {@link http://haml-lang.com/ Haml} class.
 * Does not parse the filtered text. This is useful for large blocks of text
 * without HTML tags when lines are not to be parsed.
 * @package			PHamlP
 * @subpackage	Haml.filters
 */
class HamlPlainFilter extends HamlBaseFilter {
	/**
	 * Run the filter
	 * @param string text to filter
	 * @return string filtered text
	 */
	public function run($text) {
	  return preg_replace(HamlParser::MATCH_INTERPOLATION, '<?php echo \1; ?>', $text). "\n";
	}
}