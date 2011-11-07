<?php
/* SVN FILE: $Id: SassVariableNode.php 118 2010-09-21 09:45:11Z chris.l.yates@gmail.com $ */
/**
 * SassVariableNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassVariableNode class.
 * Represents a variable.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassVariableNode extends SassNode {
	const MATCH = '/^([!$])([\w-]+)\s*:?\s*((\|\|)?=)?\s*(.+?)\s*(!default)?;?$/i';
	const IDENTIFIER = 1;
	const NAME = 2;
	const SASS_ASSIGNMENT = 3;
	const SASS_DEFAULT = 4;
	const VALUE = 5;
	const SCSS_DEFAULT = 6;
	const SASS_IDENTIFIER = '!';
	const SCSS_IDENTIFIER = '$';

	/**
	 * @var string name of the variable
	 */
	private $name;
	/**
	 * @var string value of the variable or expression to evaluate
	 */
	private $value;
	/**
	 * @var boolean whether the variable is optionally assigned
	 */
	private $isDefault;

	/**
	 * SassVariableNode constructor.
	 * @param object source token
	 * @return SassVariableNode
	 */
	public function __construct($token) {
		parent::__construct($token);
		preg_match(self::MATCH, $token->source, $matches);
		if (empty($matches[self::NAME]) || ($matches[self::VALUE] === '')) {
			throw new SassVariableNodeException('Invalid variable definition; name and expression required', array(), $this);			
		}
		$this->name = $matches[self::NAME];
		$this->value = $matches[self::VALUE];
		$this->isDefault = (!empty($matches[self::SASS_DEFAULT]) || !empty($matches[self::SCSS_DEFAULT]));
		
		// Warn about deprecated features
		if ($matches[self::IDENTIFIER] === self::SASS_IDENTIFIER) {
			$this->addWarning('Variables prefixed with "!" is deprecated; use "${name}"', array('{name}'=>$this->name));
		}
		if (!empty($matches[SassVariableNode::SASS_ASSIGNMENT])) {
			$this->addWarning('Setting variables with "{sassDefault}=" is deprecated; use "${name}: {value}{scssDefault}"', array('{sassDefault}'=>(!empty($matches[SassVariableNode::SASS_DEFAULT])?'||':''), '{name}'=>$this->name, '{value}'=>$this->value, '{scssDefault}'=>(!empty($matches[SassVariableNode::SASS_DEFAULT])?' !default':'')));
		}		
	}

	/**
	 * Parse this node.
	 * Sets the variable in the current context.
	 * @param SassContext the context in which this node is parsed
	 * @return array the parsed node - an empty array
	 */
	public function parse($context) {
		if (!$this->isDefault || !$context->hasVariable($this->name)) {
				$context->setVariable(
					$this->name, $this->evaluate($this->value, $context)
				);
		}		
		$this->parseChildren($context); // Parse any warnings
		return array();
	}

	/**
	 * Returns a value indicating if the token represents this type of node.
	 * @param object token
	 * @return boolean true if the token represents this type of node, false if not
	 */
	public static function isa($token) {
		return $token->source[0] === self::SASS_IDENTIFIER || $token->source[0] === self::SCSS_IDENTIFIER;
	}
}