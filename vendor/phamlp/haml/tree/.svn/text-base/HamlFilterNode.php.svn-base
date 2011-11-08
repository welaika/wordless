<?php
/* SVN FILE: $Id$ */
/**
 * HamlFilterNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.tree
 */

/**
 * HamlFilterNode class.
 * Represent a filter in the Haml source.
 * The filter is run on the output from child nodes when the node is rendered.
 * @package			PHamlP
 * @subpackage	Haml.tree
 */
class HamlFilterNode extends HamlNode {
	/**
	 * @var HamlBaseFilter the filter to run
	 */
	private $filter;

	/**
	 * HamlFilterNode constructor.
	 * Sets the filter.
	 * @param HamlBaseFilter the filter to run
	 * @return HamlFilterNode
	 */
	public function __construct($filter, $parent) {
	  $this->filter = $filter;	  
	  $this->parent = $parent;
	  $this->root = $parent->root;
	  $parent->children[] = $this;
	}

	/**
	* Render this node.
	* The filter is run on the content of child nodes before being returned.
	* @return string the rendered node
	*/
	public function render() {
		$output = '';
		foreach ($this->children as $child) {
			$output .= $child->getContent();
		} // foreach
		return $this->debug($this->filter->run($output));
	}
}