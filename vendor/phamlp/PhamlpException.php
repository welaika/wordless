<?php
/* SVN FILE: $Id: HamlException.php 61 2010-04-16 10:19:59Z chris.l.yates $ */
/**
 * Phamlp exception.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 */

require_once('Phamlp.php');

/**
 * Phamlp exception class.
 * Base class for PHamlP::Haml and PHamlP::Sass exceptions.
 * Translates exception messages.
 * @package			PHamlP
 */
class PhamlpException extends Exception {
	/**
	 * Phamlp Exception.
	 * @param string Category (haml|sass)
	 * @param string Exception message
	 * @param array parameters to be applied to the message using <code>strtr</code>.
	 */
	public function __construct($category, $message, $params, $object) {
		parent::__construct(Phamlp::t($category, $message, $params) . 
			(is_object($object) ? ": {$object->filename}::{$object->line}\nSource: {$object->source}" : '')
		);
	}
}