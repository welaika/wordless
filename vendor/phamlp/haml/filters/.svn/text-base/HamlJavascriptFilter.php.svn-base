<?php
/* SVN FILE: $Id$ */
/**
 * Javascript Filter for {@link http://haml-lang.com/ Haml} class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright		Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.filters
 */

/**
 * Javascript Filter for {@link http://haml-lang.com/ Haml} class.
 * Surrounds the filtered text with <script> and CDATA tags.
 * Useful for including inline Javascript.
 * Code to be interpolated can be included by wrapping it in #().
 * @package			PHamlP
 * @subpackage	Haml.filters
 */
class HamlJavascriptFilter extends HamlBaseFilter {
	/**
	 * Run the filter
	 * @param string text to filter
	 * @return string filtered text
	 */
	public function run($text) {
	  return "<script type=\"text/javascript\">\n  //<![CDATA[\n" .
	  	preg_replace(HamlParser::MATCH_INTERPOLATION, '<?php echo \1; ?>', $text) .
	  	"  //]]>\n</script>\n";
	}
}