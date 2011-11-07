<?php
/* SVN FILE: $Id: HamlCommentNode.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * HamlCommentNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.tree
 */

/**
 * HamlCommentNode class.
 * Represents a comment, including MSIE conditional comments.
 * @package			PHamlP
 * @subpackage	Haml.tree
 */
class HamlCommentNode extends HamlNode {
	private $isConditional;

	public function __construct($content, $parent) {
	  $this->content = $content;
		$this->isConditional = (bool)preg_match('/^\[.+\]$/', $content, $matches);
		$this->parent = $parent;
	  $this->root = $parent->root;
	  $parent->children[] = $this;
	}

	public function getIsConditional() {
		return $this->isConditional;
	}

	public function render() {
		$output  = $this->renderer->renderOpenComment($this);
		foreach ($this->children as $child) {
			$output .= $child->render();
		} // foreach
		$output .= $this->renderer->renderCloseComment($this);
	  return $this->debug($output);
	}
}