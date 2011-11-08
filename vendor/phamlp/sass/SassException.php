<?php
/* SVN FILE: $Id: SassException.php 106 2010-08-29 11:11:49Z chris.l.yates@gmail.com $ */
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