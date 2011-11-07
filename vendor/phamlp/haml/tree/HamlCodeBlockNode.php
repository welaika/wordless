<?php
/* SVN FILE: $Id: HamlCodeBlockNode.php 92 2010-05-20 17:42:59Z chris.l.yates $ */
/**
 * HamlCodeBlockNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.tree
 */

require_once('HamlRootNode.php');
require_once('HamlNodeExceptions.php');

/**
 * HamlCodeBlockNode class.
 * Represents a code block - if, elseif, else, foreach, do, and while.
 * @package			PHamlP
 * @subpackage	Haml.tree
 */
class HamlCodeBlockNode extends HamlNode {
	/**
	 * @var HamlCodeBlockNode else nodes for if statements
	 */
	public $else;
	/**
	 * @var string while clause for do-while loops
	 */
	public $doWhile;

	/**
	 * Adds an "else" statement to this node.
	 * @param SassIfNode "else" statement node to add
	 * @return SassIfNode this node
	 */
	public function addElse($node) {
	  if (is_null($this->else)) {
	  	$node->root			= $this->root;
	  	$node->parent		= $this->parent;
			$this->else			= $node;
	  }
	  else {
			$this->else->addElse($node);
	  }
	  return $this;
	}

	public function render() {
		$output = $this->renderer->renderStartCodeBlock($this);
		foreach ($this->children as $child) {
			$output .= $child->render();
		} // foreach
		$output .= (empty($this->else) ?
			$this->renderer->renderEndCodeBlock($this) : $this->else->render());

	  return $this->debug($output);
	}
}