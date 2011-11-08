<?php
/* SVN FILE: $Id: HamlDoctypeNode.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * HamlDoctypeNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.tree
 */

/**
 * HamlDoctypeNode class.
 * Represents a Doctype.
 * Doctypes are always rendered on a single line with a newline.
 * @package			PHamlP
 * @subpackage	Haml.tree
 */
class HamlDoctypeNode extends HamlNode {
	/**
	 * Render this node.
	 * @return string the rendered node
	 */
	public function render() {
		return $this->debug($this->content . "\n");
	}
}