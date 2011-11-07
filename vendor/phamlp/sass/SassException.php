<?php
/* SVN FILE: $Id: SassException.php 61 2010-04-16 10:19:59Z chris.l.yates $ */
/**
 * Sass exception.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass
 */

require_once(dirname(__FILE__).'/../PhamlpException.php');

/**
 * Sass exception class.
 * @package			PHamlP
 * @subpackage	Sass
 */
class SassException extends PhamlpException {
	/**
	 * Sass Exception.
	 * @param string Exception message
	 * @param array parameters to be applied to the message using <code>strtr</code>.
	 * @param object object with source code and meta data
	 */
	public function __construct($message, $params = array(), $object = null) {
		parent::__construct('sass', $message, $params, $object);
	}
}