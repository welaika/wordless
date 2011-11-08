<?php
/* SVN FILE: $Id: HamlException.php 106 2010-08-29 11:11:49Z chris.l.yates@gmail.com $ */
/**
 * Haml exception.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml
 */

require_once(dirname(__FILE__).'/../PhamlpException.php');

/**
 * Haml exception class.
 * @package			PHamlP
 * @subpackage	Haml
 */
class HamlException extends PhamlpException {
	/**
	 * Haml Exception. 
	 * @param string Exception message
	 * @param array parameters to be applied to the message using <code>strtr</code>.
	 * @param object object with source code and meta data
	 */
	public function __construct($message, $params = array(), $object = null) {
		parent::__construct('haml', $message, $params, $object);
	}
}