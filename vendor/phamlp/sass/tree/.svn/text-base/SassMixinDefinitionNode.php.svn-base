<?php
/* SVN FILE: $Id$ */
/**
 * SassMixinDefinitionNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassMixinDefinitionNode class.
 * Represents a Mixin definition.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassMixinDefinitionNode extends SassNode {
	const NODE_IDENTIFIER = '=';
	const MATCH = '/^(=|@mixin\s+)([-\w]+)\s*(?:\((.+?)\))?\s*$/i';
	const IDENTIFIER = 1;
	const NAME = 2;
	const ARGUMENTS = 3;

	/**
	 * @var string name of the mixin
	 */
	private $name;
	/**
	 * @var array arguments for the mixin as name=>value pairs were value is the
	 * default value or null for required arguments
	 */
	private $args = array();

	/**
	 * SassMixinDefinitionNode constructor.
	 * @param object source token
	 * @return SassMixinDefinitionNode
	 */
	public function __construct($token) {
  	if ($token->level !== 0) {
			throw new SassMixinDefinitionNodeException('Mixins can only be defined at root level', array(), $this);
	 	}
		parent::__construct($token);
		preg_match(self::MATCH, $token->source, $matches);
		if (empty($matches)) {
			throw new SassMixinDefinitionNodeException('Invalid {what}', array('{what}'=>'Mixin'), $this);
		}
		$this->name = $matches[self::NAME];
	  if (isset($matches[self::ARGUMENTS])) {
		  foreach (explode(',', $matches[self::ARGUMENTS]) as $arg) {
	  		$arg = explode(
	  				($matches[self::IDENTIFIER] === self::NODE_IDENTIFIER ? '=' : ':'),
	  				trim($arg)
	  		);
	  		$this->args[substr(trim($arg[0]), 1)] = (count($arg) == 2 ? trim($arg[1]) : null);
		  } // foreach
	  }
	}

	/**
	 * Parse this node.
	 * Add this mixin to  the current context.
	 * @param SassContext the context in which this node is parsed
	 * @return array the parsed node - an empty array
	 */
	public function parse($context) {
		$context->addMixin($this->name, $this);
		return array();
	}

	/**
	 * Returns the arguments with default values for this mixin
	 * @return array the arguments with default values for this mixin
	 */
	public function getArgs() {
	  return $this->args;
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
