<?php
/* SVN FILE: $Id: SassCommentNode.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassCommentNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassCommentNode class.
 * Represents a CSS comment.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassCommentNode extends SassNode {
	const NODE_IDENTIFIER = '/';
	const MATCH = '%^/\*\s*(.*?)\s*(\*/)?$%s';
	const COMMENT = 1;
	
	private $value; 
	
	/**
	 * SassCommentNode constructor.
	 * @param object source token
	 * @return CommentNode
	 */
	public function __construct($token) {
		parent::__construct($token);		
		preg_match(self::MATCH, $token->source, $matches);
		$this->value = $matches[self::COMMENT];
	}
	
	protected function getValue() {
		return $this->value; 
	} 

	/**
	 * Parse this node.
	 * @return array the parsed node - an empty array
	 */
	public function parse($context) {
		return array($this);
	}

	/**
	 * Render this node.
	 * @return string the rendered node
	 */
	public function render() {
		return $this->renderer->renderComment($this);
	}

	/**
	 * Returns a value indicating if the token represents this type of node.
	 * @param object token
	 * @return boolean true if the token represents this type of node, false if not
	 */
	public static function isa($token) {
		return $token->source[0] === self::NODE_IDENTIFIER;
	}
}