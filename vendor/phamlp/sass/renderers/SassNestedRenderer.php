<?php
/* SVN FILE: $Id: SassNestedRenderer.php 118 2010-09-21 09:45:11Z chris.l.yates@gmail.com $ */
/**
 * SassNestedRenderer class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.renderers
 */

require_once('SassExpandedRenderer.php');

/**
 * SassNestedRenderer class.
 * Nested style is the default Sass style, because it reflects the structure of
 * the document in much the same way Sass does. Each rule is indented based on
 * how deeply it's nested. Each property has its own line and is indented
 * within the rule. 
 * @package			PHamlP
 * @subpackage	Sass.renderers
 */
class SassNestedRenderer extends SassExpandedRenderer {	
	/**
	 * Renders the brace at the end of the rule
	 * @return string the brace between the rule and its properties
	 */
	protected function end() {
	  return " }\n";
	}
	
	/**
	 * Returns the indent string for the node
	 * @param SassNode the node being rendered
	 * @return string the indent string for this SassNode
	 */
	protected function getIndent($node) {
		return str_repeat(self::INDENT, $node->level);
	}

	/**
	 * Renders a directive.
	 * @param SassNode the node being rendered
	 * @param array properties of the directive
	 * @return string the rendered directive
	 */
	public function renderDirective($node, $properties) {
		$directive = $this->getIndent($node) . $node->directive . $this->between() . $this->renderProperties($properties);
		return preg_replace('/(.*})\n$/', '\1', $directive) . $this->end();
	}

	/**
	 * Renders rule selectors.
	 * @param SassNode the node being rendered
	 * @return string the rendered selectors
	 */
	protected function renderSelectors($node) {
		$indent = $this->getIndent($node);
	  return $indent.join(",\n$indent", $node->selectors);
	}
}