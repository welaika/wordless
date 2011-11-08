<?php
/* SVN FILE: $Id: SassMixinNode.php 118 2010-09-21 09:45:11Z chris.l.yates@gmail.com $ */
/**
 * SassMixinNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassMixinNode class.
 * Represents a Mixin.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassMixinNode extends SassNode {
	const NODE_IDENTIFIER = '+';
	const MATCH = '/^(\+|@include\s+)([-\w]+)\s*(?:\((.*?)\))?$/i';
	const IDENTIFIER = 1;
	const NAME = 2;
	const ARGS = 3;

	/**
	 * @var string name of the mixin
	 */
	private $name;
	/**
	 * @var array arguments for the mixin
	 */
	private $args = array();

	/**
	 * SassMixinDefinitionNode constructor.
	 * @param object source token
	 * @return SassMixinNode
	 */
	public function __construct($token) {
		parent::__construct($token);
		preg_match(self::MATCH, $token->source, $matches);
		$this->name = $matches[self::NAME];
	  if (isset($matches[self::ARGS])) {
	  	$this->args = SassScriptFunction::extractArgs($matches[self::ARGS]);
	  }
	}

	/**
	 * Parse this node.
	 * Set passed arguments and any optional arguments not passed to their
	 * defaults, then render the children of the mixin definition.
	 * @param SassContext the context in which this node is parsed
	 * @return array the parsed node
	 */
	public function parse($context) {
		$mixin = $context->getMixin($this->name);

		$context = new SassContext($context);
		$argc = count($this->args);
		$count = 0;
		foreach ($mixin->args as $name=>$value) {
			if ($count < $argc) {
				$context->setVariable($name, $this->evaluate($this->args[$count++], $context));
			}
			elseif (!is_null($value)) {
				$context->setVariable($name, $this->evaluate($value, $context));
			}
			else {
				throw new SassMixinNodeException("Mixin::{mname}: Required variable ({vname}) not given.\nMixin defined: {dfile}::{dline}\nMixin used", array('{vname}'=>$name, '{mname}'=>$this->name, '{dfile}'=>$mixin->token->filename, '{dline}'=>$mixin->token->line), $this);
			}
		} // foreach

		$children = array();
		foreach ($mixin->children as $child) {
			$child->parent = $this;
			$children = array_merge($children, $child->parse($context));
		} // foreach

		$context->merge();
		return $children;
	}

	/**
	 * Returns a value indicating if the token represents this type of node.
	 * @param object token
	 * @return boolean true if the token represents this type of node, false if not
	 */
	public static function isa($token) {
		return $token->source[0] === self::NODE_IDENTIFIER;
	}
}
