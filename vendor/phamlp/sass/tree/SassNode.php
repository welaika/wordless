<?php
/* SVN FILE: $Id: SassNode.php 118 2010-09-21 09:45:11Z chris.l.yates@gmail.com $ */
/**
 * SassNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

require_once('SassContext.php');
require_once('SassCommentNode.php');
require_once('SassDebugNode.php');
require_once('SassDirectiveNode.php');
require_once('SassImportNode.php');
require_once('SassMixinNode.php');
require_once('SassMixinDefinitionNode.php');
require_once('SassPropertyNode.php');
require_once('SassRootNode.php');
require_once('SassRuleNode.php');
require_once('SassVariableNode.php');
require_once('SassExtendNode.php');
require_once('SassForNode.php');
require_once('SassIfNode.php');
require_once('SassElseNode.php');
require_once('SassWhileNode.php');
require_once('SassNodeExceptions.php');

/**
 * SassNode class.
 * Base class for all Sass nodes.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassNode {
	/**
	 * @var SassNode parent of this node
	 */
	protected $parent;
	/**
	 * @var SassNode root node
	 */
	protected $root;
	/**
	 * @var array children of this node
	 */
	protected $children = array();
	/**
	 * @var object source token
	 */
	protected $token;
	
	/**
	 * Constructor.
	 * @param object source token
	 * @return SassNode
	 */
	public function __construct($token) {
		$this->token = $token;
	}
	
	/**
	 * Getter.
	 * @param string name of property to get
	 * @return mixed return value of getter function
	 */
	public function __get($name) {
		$getter = 'get' . ucfirst($name);
		if (method_exists($this, $getter)) {
			return $this->$getter();
		}
		throw new SassNodeException('No getter function for {what}', array('{what}'=>$name), $this);
	}

	/**
	 * Setter.
	 * @param string name of property to set
	 * @return mixed value of property
	 * @return SassNode this node
	 */
	public function __set($name, $value) {
		$setter = 'set' . ucfirst($name);
		if (method_exists($this, $setter)) {
			$this->$setter($value);
			return $this;
		}
		throw new SassNodeException('No setter function for {what}', array('{what}'=>$name), $this);
	}

	/**
	 * Resets children when cloned
	 * @see parse
	 */
	public function __clone() {
		$this->children = array();
	}

	/**
	 * Return a value indicating if this node has a parent
	 * @return array the node's parent
	 */
	public function hasParent() {
		return !empty($this->parent);
	}

	/**
	 * Returns the node's parent
	 * @return array the node's parent
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Adds a child to this node.
	 * @return SassNode the child to add
	 */
	public function addChild($child) {
		if ($child instanceof SassElseNode) {
			if (!$this->lastChild instanceof SassIfNode) {
				throw new SassException('@else(if) directive must come after @(else)if', array(), $child);
			}
			$this->lastChild->addElse($child);
		}
		else {
			$this->children[] = $child;
			$child->parent		= $this;
			$child->root			= $this->root;			
		}
		// The child will have children if a debug node has been added
		foreach ($child->children as $grandchild) {
			$grandchild->root = $this->root;		
		}
	}

	/**
	 * Returns a value indicating if this node has children
	 * @return boolean true if the node has children, false if not
	 */
	public function hasChildren() {
		return !empty($this->children);
	}

	/**
	 * Returns the node's children
	 * @return array the node's children
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * Returns a value indicating if this node is a child of the passed node.
	 * This just checks the levels of the nodes. If this node is at a greater
	 * level than the passed node if is a child of it.
	 * @return boolean true if the node is a child of the passed node, false if not
	 */
	public function isChildOf($node) {
		return $this->level > $node->level;
	}

	/**
	 * Returns the last child node of this node.
	 * @return SassNode the last child node of this node
	 */
	public function getLastChild() {
	  return $this->children[count($this->children) - 1];
	}

	/**
	 * Returns the level of this node.
	 * @return integer the level of this node
	 */
	private function getLevel() {
		return $this->token->level;
	}

	/**
	 * Returns the source for this node
	 * @return string the source for this node
	 */
	private function getSource() {
		return $this->token->source;
	}

	/**
	 * Returns the debug_info option setting for this node
	 * @return boolean the debug_info option setting for this node
	 */
	private function getDebug_info() {
		return $this->parser->debug_info;
	}

	/**
	 * Returns the line number for this node
	 * @return string the line number for this node
	 */
	private function getLine() {
		return $this->token->line;
	}

	/**
	 * Returns the line_numbers option setting for this node
	 * @return boolean the line_numbers option setting for this node
	 */
	private function getLine_numbers() {
		return $this->parser->line_numbers;
	}

	/**
	 * Returns vendor specific properties
	 * @return array vendor specific properties
	 */
	private function getVendor_properties() {
		return $this->parser->vendor_properties;
	}

	/**
	 * Returns the filename for this node
	 * @return string the filename for this node
	 */
	private function getFilename() {
		return $this->token->filename;
	}

	/**
	 * Returns the Sass parser.
	 * @return SassParser the Sass parser
	 */
	public function getParser() {
	  return $this->root->parser;
	}

	/**
	 * Returns the property syntax being used.
	 * @return string the property syntax being used
	 */
	public function getPropertySyntax() {
	  return $this->root->parser->propertySyntax;
	}

	/**
	 * Returns the SassScript parser.
	 * @return SassScriptParser the SassScript parser
	 */
	public function getScript() {
	  return $this->root->script;
	}

	/**
	 * Returns the renderer.
	 * @return SassRenderer the renderer
	 */
	public function getRenderer() {
	  return $this->root->renderer;
	}

	/**
	 * Returns the render style of the document tree.
	 * @return string the render style of the document tree
	 */
	public function getStyle() {
	  return $this->root->parser->style;
	}

	/**
	 * Returns a value indicating whether this node is in a directive
	 * @param boolean true if the node is in a directive, false if not
	 */
	public function inDirective() {
		return $this->parent instanceof SassDirectiveNode ||
				$this->parent instanceof SassDirectiveNode;
	}

	/**
	 * Returns a value indicating whether this node is in a SassScript directive
	 * @param boolean true if this node is in a SassScript directive, false if not
	 */
	public function inSassScriptDirective() {
		return $this->parent instanceof SassForNode ||
				$this->parent->parent instanceof SassForNode ||
				$this->parent instanceof SassIfNode ||
				$this->parent->parent instanceof SassIfNode ||
				$this->parent instanceof SassWhileNode ||
				$this->parent->parent instanceof SassWhileNode;
	}

	/**
	 * Evaluates a SassScript expression.
	 * @param string expression to evaluate
	 * @param SassContext the context in which the expression is evaluated
	 * @return SassLiteral value of parsed expression
	 */
	protected function evaluate($expression, $context, $x=null) {
		$context->node = $this;
		return $this->script->evaluate($expression, $context, $x);
	}

	/**
	 * Replace interpolated SassScript contained in '#{}' with the parsed value.
	 * @param string the text to interpolate
	 * @param SassContext the context in which the string is interpolated
	 * @return string the interpolated text
	 */
	protected function interpolate($expression, $context) {
		$context->node = $this;
		return $this->script->interpolate($expression, $context);
	}
	
	/**
	 * Adds a warning to the node. 
	 * @param string warning message
	 * @param array line
	 */
	public function addWarning($message, $params=array()) {
		$warning = new SassDebugNode($this->token, $message, $params);
		$this->addChild($warning);
	}

	/**
	 * Parse the children of the node.
	 * @param SassContext the context in which the children are parsed
	 * @return array the parsed child nodes
	 */
	protected function parseChildren($context) {
		$children = array();
		foreach ($this->children as $child) {
			$children = array_merge($children, $child->parse($context));
		} // foreach
		return $children; 
	}

	/**
	 * Returns a value indicating if the token represents this type of node.
	 * @param object token
	 * @return boolean true if the token represents this type of node, false if not
	 */
	public static function isa($token) {
		throw new SassNodeException('Child classes must override this method');
	}
}