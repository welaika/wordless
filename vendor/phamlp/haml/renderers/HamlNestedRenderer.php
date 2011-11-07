<?php
/* SVN FILE: $Id: HamlNestedRenderer.php 72 2010-04-20 00:41:36Z chris.l.yates $ */
/**
 * HamlNestedRenderer class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.renderers
 */

/**
 * HamlNestedRenderer class.
 * Blocks and content are indented according to their nesting level.
 * @package			PHamlP
 * @subpackage	Haml.renderers
 */
class HamlNestedRenderer extends HamlRenderer {
	/**
	 * Renders the opening tag of an element
	 */
	public function renderOpeningTag($node) {
	  return ($node->whitespaceControl['outer'] ? '' : $this->getIndent($node)) .
	  	parent::renderOpeningTag($node) .	($node->whitespaceControl['inner'] ? '' :
	  	($node->isSelfClosing && $node->whitespaceControl['outer'] ? '' : "\n"));
	}

	/**
	 * Renders the closing tag of an element
	 */
	public function renderClosingTag($node) {
	  return ($node->isSelfClosing ? '' : ($node->whitespaceControl['inner'] ? '' :
	  	$this->getIndent($node)) . parent::renderClosingTag($node) .
	  	($node->whitespaceControl['outer'] ? '' : "\n"));
	}

	/**
	 * Renders content.
	 * @param HamlNode the node being rendered
	 * @return string the rendered content
	 */
	public function renderContent($node) {
	  return $this->getIndent($node) . parent::renderContent($node) . "\n";
	}

	/**
	 * Renders the opening of a comment
	 */
	public function renderOpenComment($node) {
		return parent::renderOpenComment($node) . (empty($node->content) ? "\n" : '');
	}

	/**
	 * Renders the closing of a comment
	 */
	public function renderCloseComment($node) {
		return parent::renderCloseComment($node) . "\n";
	}

	/**
	 * Renders the start of a code block
	 */
	public function renderStartCodeBlock($node) {
		return $this->getIndent($node) . parent::renderStartCodeBlock($node) . "\n";
	}

	/**
	 * Renders the end of a code block
	 */
	public function renderEndCodeBlock($node) {
		return $this->getIndent($node) . parent::renderEndCodeBlock($node) . "\n";
	}

	private function getIndent($node) {
	  return str_repeat(' ', 2 * $node->line['indentLevel']);
	}
}