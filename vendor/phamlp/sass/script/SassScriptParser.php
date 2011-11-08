<?php
/* SVN FILE: $Id: SassScriptParser.php 118 2010-09-21 09:45:11Z chris.l.yates@gmail.com $ */
/**
 * SassScriptParser class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.script
 */

require_once('SassScriptLexer.php');
require_once('SassScriptParserExceptions.php');

/**
 * SassScriptParser class.
 * Parses SassScript. SassScript is lexed into {@link http://en.wikipedia.org/wiki/Reverse_Polish_notation Reverse Polish notation} by the SassScriptLexer and
 *  the calculated result returned.
 * @package			PHamlP
 * @subpackage	Sass.script
 */
class SassScriptParser {
	const MATCH_INTERPOLATION = '/(?<!\\\\)#\{(.*?)\}/';
	const DEFAULT_ENV = 0;
	const CSS_RULE = 1;
	const CSS_PROPERTY = 2;
	
	/**
	 * @var SassContext Used for error reporting
	 */
	public static $context;

	/**
	 * @var SassScriptLexer the lexer object
	 */
	private $lexer;

	/**
	* SassScriptParser constructor.
	* @return SassScriptParser
	*/
	public function __construct() {
		$this->lexer = new SassScriptLexer($this);
	}

	/**
	 * Replace interpolated SassScript contained in '#{}' with the parsed value.
	 * @param string the text to interpolate
	 * @param SassContext the context in which the string is interpolated
	 * @return string the interpolated text
	 */
	public function interpolate($string, $context) {
		for ($i = 0, $n = preg_match_all(self::MATCH_INTERPOLATION, $string, $matches);
				$i < $n; $i++) {
			$matches[1][$i] = $this->evaluate($matches[1][$i], $context)->toString();
		}
	  return str_replace($matches[0], $matches[1], $string);
	}

	/**
	 * Evaluate a SassScript.
	 * @param string expression to parse
	 * @param SassContext the context in which the expression is evaluated
	 * @param	integer the environment in which the expression is evaluated
	 * @return SassLiteral parsed value
	 */
	public function evaluate($expression, $context, $environment=self::DEFAULT_ENV) {
		self::$context = $context;
		$operands = array();

		$tokens = $this->parse($expression, $context, $environment);
		
		while (count($tokens)) {
			$token = array_shift($tokens);
			if ($token instanceof SassScriptFunction) {
				array_push($operands, $token->perform());
			}
			elseif ($token instanceof SassLiteral) {
				if ($token instanceof SassString) {
					$token = new SassString($this->interpolate($token->toString(), self::$context));
				}
				array_push($operands, $token);
			}
			else {
				$args = array();
				for ($i = 0, $c = $token->operandCount; $i < $c; $i++) {
					$args[] = array_pop($operands);
				}
				array_push($operands, $token->perform($args));
			}
		}
	  return array_shift($operands);
	}

	/**
	 * Parse SassScript to a set of tokens in RPN
	 * using the Shunting Yard Algorithm.
	 * @param string expression to parse
	 * @param SassContext the context in which the expression is parsed
	 * @param	integer the environment in which the expression is parsed
	 * @return array tokens in RPN
	 */
	public function parse($expression, $context, $environment=self::DEFAULT_ENV) {
		$outputQueue = array();
		$operatorStack = array();
		$parenthesis = 0;
		
		$tokens = $this->lexer->lex($expression, $context);

		foreach($tokens as $i=>$token) {
			// If two literals/expessions are seperated by whitespace use the concat operator
			if (empty($token)) {
				if ($i > 0 && (!$tokens[$i-1] instanceof SassScriptOperation || $tokens[$i-1]->operator === SassScriptOperation::$operators[')'][0]) &&
						(!$tokens[$i+1] instanceof SassScriptOperation || $tokens[$i+1]->operator === SassScriptOperation::$operators['('][0])) {
					$token = new SassScriptOperation(SassScriptOperation::$defaultOperator, $context);
				}
				else {
					continue;
				}				
			}
			elseif ($token instanceof SassScriptVariable) {
				$token = $token->evaluate($context);
				$environment = self::DEFAULT_ENV;
			}

			// If the token is a number or function add it to the output queue.
 			if ($token instanceof SassLiteral || $token instanceof SassScriptFunction) {
 				if ($environment === self::CSS_PROPERTY && $token instanceof SassNumber && !$parenthesis) {
					$token->inExpression = false; 
 				}
				array_push($outputQueue, $token);
			}
			// If the token is an operation
			elseif ($token instanceof SassScriptOperation) {
				// If the token is a left parenthesis push it onto the stack.
				if ($token->operator == SassScriptOperation::$operators['('][0]) {
					array_push($operatorStack, $token);
					$parenthesis++;
				}
				// If the token is a right parenthesis:
				elseif ($token->operator == SassScriptOperation::$operators[')'][0]) {
					$parenthesis--;
					while ($c = count($operatorStack)) {
						// If the token at the top of the stack is a left parenthesis
						if ($operatorStack[$c - 1]->operator == SassScriptOperation::$operators['('][0]) {
							// Pop the left parenthesis from the stack, but not onto the output queue.
							array_pop($operatorStack);
							break;
						}
						// else pop the operator off the stack onto the output queue.
						array_push($outputQueue, array_pop($operatorStack));
					}
					// If the stack runs out without finding a left parenthesis
					// there are mismatched parentheses.
					if ($c == 0) {
						throw new SassScriptParserException('Unmatched parentheses', array(), $context->node);
					}
				}
				// the token is an operator, o1, so:
				else {
					// while there is an operator, o2, at the top of the stack
					while ($c = count($operatorStack)) {
						$operation = $operatorStack[$c - 1];
						// if o2 is left parenthesis, or
						// the o1 has left associativty and greater precedence than o2, or
						// the o1 has right associativity and lower or equal precedence than o2
						if (($operation->operator == SassScriptOperation::$operators['('][0]) ||
							($token->associativity == 'l' && $token->precedence > $operation->precedence) ||
							($token->associativity == 'r' && $token->precedence <= $operation->precedence)) {
							break; // stop checking operators
						}
						//pop o2 off the stack and onto the output queue
						array_push($outputQueue, array_pop($operatorStack));
					}
					// push o1 onto the stack
					array_push($operatorStack, $token);
				}
			}
		}

		// When there are no more tokens
		while ($c = count($operatorStack)) { // While there are operators on the stack:
			if ($operatorStack[$c - 1]->operator !== SassScriptOperation::$operators['('][0]) {
				array_push($outputQueue, array_pop($operatorStack));
			}
			else {
				throw new SassScriptParserException('Unmatched parentheses', array(), $context->node);
			}
		}
		return $outputQueue;
	}
}