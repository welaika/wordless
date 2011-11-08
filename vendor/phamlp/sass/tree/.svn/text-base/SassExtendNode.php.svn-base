<?php
/* SVN FILE: $Id: SassExtendNode.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassExtendNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassExtendNode class.
 * Represents a Sass @debug or @warn directive.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassExtendNode extends SassNode {
	const IDENTIFIER = '@';
	const MATCH = '/^@extend\s+(.+)/i';
	const VALUE = 1;

	/**
	 * @var string the directive
	 */
	private $value;

	/**
	 * SassExtendNode.
	 * @param object source token
	 * @return SassExtendNode
	 */
	public function __construct($token) {
		parent::__construct($token);
		preg_match(self::MATCH, $token->source, $matches);
		$this->value = $matches[self::VALUE];
	}

	/**
	 * Parse this node.
	 * @return array An empty array
	 */
	public function parse($context) {
		$this->root->extend($this->value, $this->parent->selectors);
		return array();
	}
}