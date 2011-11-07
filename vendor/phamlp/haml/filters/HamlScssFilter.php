<?php
/* SVN FILE: $Id: HamlSassFilter.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * {@link Scss http://sass-lang.com/} Filter for
 * {@link http://haml-lang.com/ Haml} class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright		Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.filters
 */

require_once('HamlCssFilter.php');
require_once(dirname(__FILE__).'/../../sass/SassParser.php');

/**
 * {@link Sass http://sass-lang.com/} Filter for
 * {@link http://haml-lang.com/ Haml} class.
 * Parses the text as Sass then calls the CSS filter.
 * Useful for including inline Sass.
 * @package			PHamlP
 * @subpackage	Haml.filters
 */
class HamlScssFilter extends HamlBaseFilter {
	/**
	 * Run the filter
	 * @param string text to filter
	 * @return string filtered text
	 */
	public function run($text) {
		$sass = new SassParser(array('syntax'=>'scss'));
		$css = new HamlCssFilter();
		$css->init();

		return $css->run($sass->toCss(preg_replace(HamlParser::MATCH_INTERPOLATION, '<?php echo \1; ?>', $text), false));
	}
}