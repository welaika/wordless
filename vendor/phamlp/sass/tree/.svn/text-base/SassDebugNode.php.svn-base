<?php
/* SVN FILE: $Id: SassDebugNode.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassDebugNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassDebugNode class.
 * Represents a Sass @debug or @warn directive.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassDebugNode extends SassNode {
	const IDENTIFIER = '@';
	const MATCH = '/^@(?:debug|warn)\s+(.+?)\s*;?$/';
	const MESSAGE = 1;

	/**
	 * @var string the debug/warning message
	 */
	private $message;
	/**
	 * @var array parameters for the message;
	 * only used by internal warning messages
	 */
	private $params;
	/**
	 * @var boolean true if this is a warning
	 */
	private $warning;

	/**
	 * SassDebugNode.
	 * @param object source token
	 * @param mixed string: an internally generated warning message about the
	 * source
	 * boolean: the source token is a @debug or @warn directive containing the
	 * message; True if this is a @warn directive
	 * @param array parameters for the message
	 * @return SassDebugNode
	 */
	public function __construct($token, $message=false, $params=array()) {
		parent::__construct($token);
		if (is_string($message)) {
			$this->message = $message;
			$this->warning = true;
		}
		else {
			preg_match(self::MATCH, $token->source, $matches);
			$this->message = $matches[self::MESSAGE];
			$this->warning = $message;			
		}
		$this->params = $params;
	}

	/**
	 * Parse this node.
	 * This raises an error.
	 * @return array An empty array
	 */
	public function parse($context) {
		if (!$this->warning || ($this->root->parser->quiet === false)) {
			set_error_handler(array($this, 'errorHandler'));
			trigger_error(($this->warning ? $this->interpolate(Phamlp::t('sass', $this->message, $this->params), $context) : $this->evaluate(Phamlp::t('sass', $this->message, $this->params), $context)->toString()));
			restore_error_handler();			
		}

		return array();
	}

	/**
	 * Error handler for degug and warning statements.
	 * @param int Error number 
	 * @param string Message 
	 */
	public function errorHandler($errno, $message) {
		echo '<div style="background-color:#ce4dd6;border-bottom:1px dashed #88338d;color:white;font:10pt verdana;margin:0;padding:0.5em 2%;width:96%;"><p style="height:auto;margin:0.25em 0;padding:0;width:100%;"><span style="font-weight:bold;">SASS '.($this->warning ? 'WARNING' : 'DEBUG').":</span> $message</p><p style=\"margin:0.1em;padding:0;padding-left:0.5em;width:100%;\">{$this->filename}::{$this->line}</p><p style=\"margin:0.1em;padding:0;padding-left:0.5em;width:100%;\">Source: {$this->source}</p></div>";
	}
}