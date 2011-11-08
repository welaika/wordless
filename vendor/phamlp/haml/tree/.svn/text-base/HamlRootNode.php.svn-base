<?php
/* SVN FILE: $Id$ */
/**
 * HamlRootNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.tree
 */

require_once(dirname(__FILE__).'/../renderers/HamlRenderer.php');

/**
 * HamlRootNode class.
 * Also the root node of a document.
 * @package			PHamlP
 * @subpackage	Haml.tree
 */
class HamlRootNode extends HamlNode {
	/**
	 * @var HamlRenderer the renderer for this node
	 */
	protected $renderer;
	/**
	 * @var array options
	 */
	protected $options;

	/**
	 * Root HamlNode constructor.
	 * @param array options for the tree
	 * @return HamlNode
	 */
	public function __construct($options) {
		$this->root = $this;
		$this->options = $options;
		$this->renderer = HamlRenderer::getRenderer($this->options['style'],
			array(
				'format' => $this->options['format'],
				'attrWrapper' => $this->options['attrWrapper'],
				'minimizedAttributes' => $this->options['minimizedAttributes'],
			)
		);
		$this->token = array('level' => -1);
	}

	/**
	 * Render this node.
	 * @return string the rendered node
	 */
	public function render() {
		foreach ($this->children as $child) {
			$this->output .= $child->render();
		} // foreach
		return $this->output;
	}
}
