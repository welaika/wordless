<?php
/* SVN FILE: $Id: HamlCdataFilter.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * CDATA Filter for {@link http://haml-lang.com/ Haml} class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright		Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.filters
 */

/**
 * CDATA Filter for {@link http://haml-lang.com/ Haml} class.
 * Surrounds the filtered text with CDATA tags.
 * @package			PHamlP
 * @subpackage	Haml.filters
 */
class HamlCdataFilter extends HamlBaseFilter {
	/**
	 * Run the filter
	 * @param string text to filter
	 * @return string filtered text
	 */
	public function run($text) {
	  return "<![CDATA[\n" .
	  	preg_replace(HamlParser::MATCH_INTERPOLATION, '<?php echo \1; ?>', $text) .
	  	"  ]]>\n";
	}
}
