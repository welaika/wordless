<?php
/* SVN FILE: $Id: HamlElementNode.php 83 2010-05-17 16:35:54Z chris.l.yates $ */
/**
 * HamlElementNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.tree
 */

require_once('HamlRootNode.php');
require_once('HamlNodeExceptions.php');

/**
 * HamlElementNode class.
 * Represents an element.
 * @package			PHamlP
 * @subpackage	Haml.tree
 */
class HamlElementNode extends HamlNode {
	public $isBlock;
	public $isSelfClosing;
	public $attributes;
	public $whitespaceControl;
	public $escapeHTML;

	public function render() {
		$renderer = $this->renderer;
		$this->output = $renderer->renderOpeningTag($this);
		$close = $renderer->renderClosingTag($this);
		
		if ($this->whitespaceControl['outer']['left']) {
			$this->output = ltrim($this->output);
			$close = rtrim($close);
			$this->parent->output = rtrim($this->parent->output);
		}

		foreach ($this->children as $index=>$child) {
			$output = $child->render();
			$output = ($this->whitespaceControl['inner'] ? trim($output) : $output);
			if ($index && $this->children[$index-1] instanceof HamlElementNode && $this->children[$index-1]->whitespaceControl['outer']['right']) {
				$output = ltrim($output);
			}
			$this->output .= $output;
		} // foreach

		return $this->debug($this->output .	(isset($child) &&
			$child instanceof HamlElementNode &&
			$child->whitespaceControl['outer']['right'] ? ltrim($close) : $close));
	}
}