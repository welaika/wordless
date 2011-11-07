<?php
/* SVN FILE: $Id: SassIfNode.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * SassElseNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassElseNode class.
 * Represents Sass Else If and Else statements.
 * Else If and Else statement nodes are chained below the If statement node.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassElseNode extends SassIfNode {
	/**
	 * SassElseNode constructor.
	 * @param object source token
	 * @return SassElseNode
	 */
	public function __construct($token) {
		parent::__construct($token, false);
	}
}