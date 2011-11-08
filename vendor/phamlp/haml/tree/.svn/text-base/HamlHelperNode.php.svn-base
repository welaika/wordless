<?php
/* SVN FILE: $Id$ */
/**
 * HamlHelperNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.tree
 */

/**
 * HamlHelperNode class.
 * Represent a helper in the Haml source.
 * The helper is run on the output from child nodes when the node is rendered.
 * @package			PHamlP
 * @subpackage	Haml.tree
 */
class HamlHelperNode extends HamlNode {
	const MATCH = '/(.*?)(\w+)\((.+?)\)(?:\s+(.*))?$/';
	const PRE = 1;
	const NAME = 2;
	const ARGS = 3;
	const BLOCK = 4;
	
	/**
	 * @var string the helper class name
	 */
	private $class;
	/**
	 * @var string helper method name
	 */
	private $pre;
	/**
	 * @var string helper method name
	 */
	private $name;
	/**
	 * @var string helper method arguments
	 */
	private $args;

	/**
	 * HamlFilterNode constructor.
	 * Sets the filter.
	 * @param string helper class.
	 * @param string helper call.
	 * @return HamlHelperNode
	 */
	public function __construct($class, $pre, $name, $args, $parent) {
	  $this->class = $class;
	  $this->pre = $pre;
	  $this->name = $name;
	  $this->args = $args;
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
		$children = '';
		foreach ($this->children as $child) {
			$children .= trim($child->render());
		} // foreach
		$output = '<?php '.(empty($this->pre) ? 'echo' : $this->pre)." {$this->class}::{$this->name}('$children',{$this->args}); ?>";
		return $this->debug($output);
	}
}