<?php
/* SVN FILE: $Id: Sass.php 64 2010-04-16 13:23:14Z chris.l.yates $ */
/**
 * Sass class file.
 * Parses {@link Sass http://sass-lang.com/} files.
 * Please see the {@link Sass documentation http://sass-lang.com/docs} for
 * details of Sass.
 *
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright		Copyright &copy; 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Yii
 */

 // Do NOT use Yii::import. Doing so causes conflict with HamlSassFilter
require_once(Yii::getPathOfAlias('ext.phamlp.vendors.phamlp.sass').DIRECTORY_SEPARATOR.'SassParser.php');

/**
 * Sass class
 * @package			PHamlP
 * @subpackage	Yii
 */
class Sass {
	/**
	 * @var SassParser
	 */
	private $sass;

	/**
	 * Constructor
	 * @param array Sass options
	 * @return Sass
	 */
	public function __construct($options) {
	  $this->sass = new SassParser($options);
	}

	/**
	 * Parse a Sass file to CSS
	 * @param string path to file
	 * @return string CSS
	 */
	public function parse($file) {
	  return $this->sass->toCss($file);
	}
}