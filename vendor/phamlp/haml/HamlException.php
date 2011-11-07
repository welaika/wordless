<?php
/* SVN FILE: $Id: HamlException.php 61 2010-04-16 10:19:59Z chris.l.yates $ */
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