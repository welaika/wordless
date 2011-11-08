<?php
/* SVN FILE: $Id: SassExpandedRenderer.php 118 2010-09-21 09:45:11Z chris.l.yates@gmail.com $ */
/**
 * SassExpandedRenderer class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.renderers
 */

require_once('SassCompactRenderer.php');

/**
 * SassExpandedRenderer class.
 * Expanded is the typical human-made CSS style, with each property and rule
 * taking up one line. Properties are indented within the rules, but the rules
 * are not indented in any special way.
 * @package			PHamlP
 * @subpackage	Sass.renderers
 */
class SassExpandedRenderer extends SassCompactRenderer {
	/**
	 * Renders the brace between the selectors and the properties
	 * @return string the brace between the selectors and the properties
	 */
	protected function between() {
	  return " {\n" ;
	}
	
	/**
	 * Renders the brace at the end of the rule
	 * @return string the brace between the rule and its properties
	 */
	protected function end() {
	  return "\n}\n\n";
	}

	/**
	 * Renders a comment.
	 * @param SassNode the node being rendered
	 * @return string the rendered commnt
	 */
	public function renderComment($node) {
		$indent = $this->getIndent($node);
		$lines = explode("\n", $node->value);
		foreach ($lines as &$line) {
			$line = trim($line);
		}
		return "$indent/*\n$indent * ".join("\n$indent * ", $lines)."\n$indent */".(empty($indent)?"\n":'');
	}

	/**
	 * Renders properties.
	 * @param array properties to render
	 * @return string the rendered properties
	 */
	public function renderProperties($node, $properties) {
		$indent = $this->getIndent($node).self::INDENT;
		return $indent.join("\n$indent", $properties);
	}
}
