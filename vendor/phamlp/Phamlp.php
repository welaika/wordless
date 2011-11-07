<?php
/* SVN FILE: $Id: HamlException.php 61 2010-04-16 10:19:59Z chris.l.yates $ */
/**
 * Phamlp.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 */
/**
 * Phamlp class.
 * Static support classes.
 * @package			PHamlP
 */
class Phamlp {
	/**
	 * @var string Language used to translate messages
	 */
	public static $language;
	/**
	 * @var array Messages used for translation
	 */
	public static $messages;
	
	/**
	 * Translates a message to the specified language.
	 * @param string message category.
	 * @param string the original message
	 * @param array parameters to be applied to the message using <code>strtr</code>.
	 * @return string the translated message
	 */
	public static function t($category, $message, $params = array()) {
		if (!empty(self::$language)) {
			$message = self::translate($category, $message);
		}
		return $params!==array() ? strtr($message,$params) : $message;
	}

	/**
	 * Translates a message to the specified language.
	 * If the language or the message in the specified language is not defined the
	 * original message is returned.
	 * @param string message category
	 * @param string the original message
	 * @return string the translated message
	 */
	private static function translate($category, $message) {
		if (empty(self::$messages[$category])) self::loadMessages($category);
		return (empty(self::$messages[$category][$message]) ? $message : self::$messages[$category][$message]);
	}

	/**
	 * Loads the specified language message file for translation.
	 * Message files are PHP files in the "category/messages" directory and named
	 * "language.php", where category is either haml or sass, and language is the
	 * specified language.
	 * The message file returns an array of (source, translation) pairs; for example:
	 * <pre>
	 * return array(
	 *   'original message 1' => 'translated message 1',
	 *   'original message 2' => 'translated message 2',
	 * );
	 * </pre>
	 * @param string message category
	 */
	private static function loadMessages($category) {
		$messageFile = dirname(__FILE__).DIRECTORY_SEPARATOR.$category.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.self::$language.'.php';
		if (file_exists($messageFile)) {
			self::$messages[$category] = require_once($messageFile);
		}
	}
}