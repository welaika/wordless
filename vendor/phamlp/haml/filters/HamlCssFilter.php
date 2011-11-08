<?php
/* SVN FILE: $Id: HamlCssFilter.php 99 2010-06-13 14:12:08Z chris.l.yates $ */
/**
 * CSS Filter for {@link http://haml-lang.com/ Haml} class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright		Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.filters
 */

/**
 * CSS Filter for {@link http://haml-lang.com/ Haml} class.
 * Surrounds the filtered text with <style> and CDATA tags.
 * Useful for including inline CSS.
 * @package			PHamlP
 * @subpackage	Haml.filters
 */
class HamlCssFilter extends HamlBaseFilter {
	/**
	 * Run the filter
	 * @param string text to filter
	 * @return string filtered text
	 */
	public function run($text) {
	  return "<style type=\"text/css\">\n/*<![CDATA[*/\n" .
	  	preg_replace(HamlParser::MATCH_INTERPOLATION, '<?php echo \1; ?>', $text) .
	  	"/*]]>*/\n</style>\n";
	}
}