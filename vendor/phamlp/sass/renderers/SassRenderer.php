<?php
/* SVN FILE: $Id: SassRenderer.php 68 2010-04-18 13:24:41Z chris.l.yates $ */
/**
 * SassRenderer class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.renderers
 */

require_once('SassCompactRenderer.php');
require_once('SassCompressedRenderer.php');
require_once('SassExpandedRenderer.php');
require_once('SassNestedRenderer.php');

/**
 * SassRenderer class.
 * @package			PHamlP
 * @subpackage	Sass.renderers
 */
class SassRenderer {
	/**#@+
	 * Output Styles
	 */
	const STYLE_COMPRESSED = 'compressed';
	const STYLE_COMPACT 	 = 'compact';
	const STYLE_EXPANDED 	 = 'expanded';
	const STYLE_NESTED 		 = 'nested';
	/**#@-*/

	const INDENT = '  ';

	/**
	 * Returns the renderer for the required render style.
	 * @param string render style
	 * @return SassRenderer
	 */
	public static function getRenderer($style) {
		switch ($style) {
			case self::STYLE_COMPACT:
		  	return new SassCompactRenderer();
			case self::STYLE_COMPRESSED:
		  	return new SassCompressedRenderer();
			case self::STYLE_EXPANDED:
		  	return new SassExpandedRenderer();
			case self::STYLE_NESTED:
		  	return new SassNestedRenderer();
		} // switch
	}
}