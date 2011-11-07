<?php
/* SVN FILE: $Id: HamlCompactRenderer.php 74 2010-04-20 12:20:29Z chris.l.yates $ */
/**
 * HamlCompactRenderer class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.renderers
 */

/**
 * HamlCompactRenderer class.
 * Renders blocks on single lines.
 * @package			PHamlP
 * @subpackage	Haml.renderers
 */
class HamlCompactRenderer extends HamlRenderer {
	/**
	 * Renders the opening tag of an element
	 */
	public function renderOpeningTag($node) {
	  return ($node->isBlock ? '' : ' ') . parent::renderOpeningTag($node);
	}
	
	/**
	 * Renders the closing tag of an element
	 */
	public function renderClosingTag($node) {
	  return parent::renderClosingTag($node) . ($node->isBlock ? "\n" : ' ');
	}
}