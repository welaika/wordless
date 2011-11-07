<?php
/* SVN FILE: $Id: SassNumber.php 118 2010-09-21 09:45:11Z chris.l.yates@gmail.com $ */
/**
 * SassNumber class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.script.literals
 */

require_once('SassLiteral.php');
 
/**
 * SassNumber class.
 * Provides operations and type testing for Sass numbers.
 * Units are of the passed value are converted the those of the class value
 * if it has units. e.g. 2cm + 20mm = 4cm while 2 + 20mm = 22mm.
 * @package			PHamlP
 * @subpackage	Sass.script.literals
 */
class SassNumber extends SassLiteral {
	/**
	 * Regx for matching and extracting numbers
	 */
	const MATCH = '/^((?:-)?(?:\d*\.)?\d+)(([a-z%]+)(\s*[\*\/]\s*[a-z%]+)*)?/i';
	const VALUE = 1;
	const UNITS = 2;
	/**
	 * The number of decimal digits to round to.
	 * If the units are pixels the result is always
	 * rounded down to the nearest integer.
	 */
	const PRECISION = 4;

	/**
	 * @var array Conversion factors for units using inches as the base unit
	 * (only because pt and pc are expressed as fraction of an inch, so makes the
	 * numbers easy to understand).
	 * Conversions are based on the following
	 * in: inches — 1 inch = 2.54 centimeters
   * cm: centimeters
   * mm: millimeters
   * pc: picas — 1 pica = 12 points
   * pt: points — 1 point = 1/72nd of an inch
	 */
	static private $unitConversion = array(
		'in' => 1,
		'cm' => 2.54,
		'mm' => 25.4,
		'pc' => 6,
		'pt' => 72
	);

	/**
	 * @var array numerator units of this number
	 */
	private $numeratorUnits = array();

	/**
	 * @var array denominator units of this number
	 */
	private $denominatorUnits = array();
	
	/**
	 * @var boolean whether this number is in an expression or a literal number
	 * Used to determine whether division should take place 
	 */
	public $inExpression = true;

	/**
	 * class constructor.
	 * Sets the value and units of the number.
	 * @param string number
	 * @return SassNumber
	 */
	public function __construct($value) {
	  preg_match(self::MATCH, $value, $matches);
	  $this->value = $matches[self::VALUE];
	  if (!empty($matches[self::UNITS])) {
			$units = explode('/', $matches[self::UNITS]);
			$numeratorUnits = $denominatorUnits = array();
			
			foreach (explode('*', $units[0]) as $unit) {
				$numeratorUnits[] = trim($unit);			
			}
			if (isset($units[1])) {
				foreach (explode('*', $units[1]) as $unit) {
					$denominatorUnits[] = trim($unit);
				}
			}
			$units = $this->removeCommonUnits($numeratorUnits, $denominatorUnits);
			$this->numeratorUnits = $units[0];			
			$this->denominatorUnits = $units[1];
	  }
	}

	/**
	 * Adds the value of other to the value of this
	 * @param mixed SassNumber|SassColour: value to add
	 * @return mixed SassNumber if other is a SassNumber or
	 * SassColour if it is a SassColour
	 */
	public function op_plus($other) {
		if ($other instanceof SassColour) {
			return $other->op_plus($this);
		}
		elseif (!$other instanceof SassNumber) {
			throw new SassNumberException('{what} must be a {type}', array('{what}'=>Phamlp::t('sass', 'Number'), '{type}'=>Phamlp::t('sass', 'number')), SassScriptParser::$context->node);
		}
		else {
			$other = $this->convert($other);
			return new SassNumber(($this->value + $other->value).$this->units);
		}
	}

	/**
	 * Unary + operator
	 * @return SassNumber the value of this number
	 */
	public function op_unary_plus() {
		return $this;
	}

	/**
	 * Subtracts the value of other from this value
	 * @param mixed SassNumber|SassColour: value to subtract
	 * @return mixed SassNumber if other is a SassNumber or
	 * SassColour if it is a SassColour
	 */
	public function op_minus($other) {
		if ($other instanceof SassColour) {
			return $other->op_minus($this);
		}
		elseif (!$other instanceof SassNumber) {
			throw new SassNumberException('{what} must be a {type}', array('{what}'=>Phamlp::t('sass', 'Number'), '{type}'=>Phamlp::t('sass', 'number')), SassScriptParser::$context->node);
		}
		else {
			$other = $this->convert($other);
			return new SassNumber(($this->value - $other->value).$this->units);
		}
	}

	/**
	 * Unary - operator
	 * @return SassNumber the negative value of this number
	 */
	public function op_unary_minus() {
		return new SassNumber(($this->value * -1).$this->units);
	}

	/**
	 * Multiplies this value by the value of other
	 * @param mixed SassNumber|SassColour: value to multiply by
	 * @return mixed SassNumber if other is a SassNumber or
	 * SassColour if it is a SassColour
	 */
	public function op_times($other) {
		if ($other instanceof SassColour) {
			return $other->op_times($this);
		}
		elseif (!$other instanceof SassNumber) {
			throw new SassNumberException('{what} must be a {type}', array('{what}'=>Phamlp::t('sass', 'Number'), '{type}'=>Phamlp::t('sass', 'number')), SassScriptParser::$context->node);
		}
		else {
			return new SassNumber(($this->value * $other->value).$this->unitString(
				array_merge($this->numeratorUnits, $other->numeratorUnits),
				array_merge($this->denominatorUnits, $other->denominatorUnits)
			));
		}
	}

	/**
	 * Divides this value by the value of other
	 * @param mixed SassNumber|SassColour: value to divide by
	 * @return mixed SassNumber if other is a SassNumber or
	 * SassColour if it is a SassColour
	 */
	public function op_div($other) {
		if ($other instanceof SassColour) {
			return $other->op_div($this);
		}
		elseif (!$other instanceof SassNumber) {
			throw new SassNumberException('{what} must be a {type}', array('{what}'=>Phamlp::t('sass', 'Number'), '{type}'=>Phamlp::t('sass', 'number')), SassScriptParser::$context->node);
		}
		elseif ($this->inExpression || $other->inExpression) {
			return new SassNumber(($this->value / $other->value).$this->unitString(
				array_merge($this->numeratorUnits, $other->denominatorUnits),
				array_merge($this->denominatorUnits, $other->numeratorUnits)
			));
		}
		else {
			return parent::op_div($other);
		}
	}
	
	/**
	 * The SassScript == operation.
	 * @return SassBoolean SassBoolean object with the value true if the values
	 * of this and other are equal, false if they are not
	 */
	public function op_eq($other) {
		if (!$other instanceof SassNumber) {
			return new SassBoolean(false);
		}
		try {
			return new SassBoolean($this->value == $this->convert($other)->value);
		}
		catch (Exception $e) {
			return new SassBoolean(false);
		}		
	}
	
	/**
	 * The SassScript > operation.
	 * @param sassLiteral the value to compare to this
	 * @return SassBoolean SassBoolean object with the value true if the values
	 * of this is greater than the value of other, false if it is not
	 */
	public function op_gt($other) {
		if (!$other instanceof SassNumber) {
			throw new SassNumberException('{what} must be a {type}', array('{what}'=>Phamlp::t('sass', 'Number'), '{type}'=>Phamlp::t('sass', 'number')), SassScriptParser::$context->node);
		}
		return new SassBoolean($this->value > $this->convert($other)->value);
	}
	
	/**
	 * The SassScript >= operation.
	 * @param sassLiteral the value to compare to this
	 * @return SassBoolean SassBoolean object with the value true if the values
	 * of this is greater than or equal to the value of other, false if it is not
	 */
	public function op_gte($other) {
		if (!$other instanceof SassNumber) {
			throw new SassNumberException('{what} must be a {type}', array('{what}'=>Phamlp::t('sass', 'Number'), '{type}'=>Phamlp::t('sass', 'number')), SassScriptParser::$context->node);
		}
		return new SassBoolean($this->value >= $this->convert($other)->value);
	}
	
	/**
	 * The SassScript < operation.
	 * @param sassLiteral the value to compare to this
	 * @return SassBoolean SassBoolean object with the value true if the values
	 * of this is less than the value of other, false if it is not
	 */
	public function op_lt($other) {
		if (!$other instanceof SassNumber) {
			throw new SassNumberException('{what} must be a {type}', array('{what}'=>Phamlp::t('sass', 'Number'), '{type}'=>Phamlp::t('sass', 'number')), SassScriptParser::$context->node);
		}
		return new SassBoolean($this->value < $this->convert($other)->value);
	}
	
	/**
	 * The SassScript <= operation.
	 * @param sassLiteral the value to compare to this
	 * @return SassBoolean SassBoolean object with the value true if the values
	 * of this is less than or equal to the value of other, false if it is not
	 */
	public function op_lte($other) {
		if (!$other instanceof SassNumber) {
			throw new SassNumberException('{what} must be a {type}', array('{what}'=>Phamlp::t('sass', 'Number'), '{type}'=>Phamlp::t('sass', 'number')), SassScriptParser::$context->node);
		}
		return new SassBoolean($this->value <= $this->convert($other)->value);
	}

	/**
	 * Takes the modulus (remainder) of this value divided by the value of other
	 * @param string value to divide by
	 * @return mixed SassNumber if other is a SassNumber or
	 * SassColour if it is a SassColour
	 */
	public function op_modulo($other) {
		if (!$other instanceof SassNumber || !$other->isUnitless()) {
			throw new SassNumberException('{what} must be a {type}', array('{what}'=>Phamlp::t('sass', 'Number'), '{type}'=>Phamlp::t('sass', 'unitless number')), SassScriptParser::$context->node);
		}
		$this->value %= $this->convert($other)->value;
		return $this;
	}

	/**
	 * Converts values and units.
	 * If this is a unitless numeber it will take the units of other; if not
	 * other is coerced to the units of this.
	 * @param SassNumber the other number
	 * @return SassNumber the other number with its value and units coerced if neccessary
	 * @throws SassNumberException if the units are incompatible
	 */
	private function convert($other) {
		if ($this->isUnitless()) {
			$this->numeratorUnits = $other->numeratorUnits;
			$this->denominatorUnits = $other->denominatorUnits;
		}
		else {
			$other = $other->coerce($this->numeratorUnits, $this->denominatorUnits);
		}
		return $other;
	}
	
	/**
	 * Returns the value of this number converted to other units.
	 * The conversion takes into account the relationship between e.g. mm and cm,
	 * as well as between e.g. in and cm.
	 * 
	 * If this number is unitless, it will simply return itself with the given units.
	 * @param array $numeratorUnits
	 * @param array $denominatorUnits
	 * @return SassNumber
	 */
	public function coerce($numeratorUnits, $denominatorUnits) {
		return new SassNumber(($this->isUnitless() ?
				$this->value :
				$this->value *
					$this->coercionFactor($this->numeratorUnits, $numeratorUnits) /
          $this->coercionFactor($this->denominatorUnits, $denominatorUnits)
		).join(' * ', $numeratorUnits) .
	  (!empty($denominatorUnits) ? ' / ' . join(' * ', $denominatorUnits) : ''));
	}
	
	/**
	 * Calculates the corecion factor to apply to the value
	 * @param array units being converted from
	 * @param array units being converted to
	 * @return float the coercion factor to apply
	 */
	private function coercionFactor($fromUnits, $toUnits) {
		$units = $this->removeCommonUnits($fromUnits, $toUnits);
		$fromUnits = $units[0];
		$toUnits = $units[1];
		
		if (sizeof($fromUnits) !== sizeof($toUnits) || !$this->areConvertable(array_merge($fromUnits, $toUnits))) {
			throw new SassNumberException("Incompatible units: '{from}' and '{to}'", array('{from}'=>join(' * ', $fromUnits), '{to}'=>join(' * ', $toUnits)), SassScriptParser::$context->node);
		}
		
		$coercionFactor = 1;
		foreach ($fromUnits as $i=>$from) {
			if (array_key_exists($from) && array_key_exists($from)) {
				$coercionFactor *=
					self::$unitConversion[$toUnits[$i]] / self::$unitConversion[$from];
			}
			else {
				throw new SassNumberException("Incompatible units: '{from}' and '{to}",
					array('{from}'=>join(' * ', $fromUnits), '{to}'=>join(' * ', $toUnits)),
					SassScriptParser::$context->node);
			}			
		}
		return $coercionFactor; 
	}
	
	/**
	 * Returns a value indicating if all the units are capable of being converted
	 * @param array units to test
	 * @return boolean true if all units can be converted, false if not
	 */
	private function areConvertable($units) {
		$convertable = array_keys(self::$unitConversion);
		foreach ($units as $unit) {
			if (!in_array($unit, $convertable))
				return false;		
		}
		return true; 
	}
	
	/**
	 * Removes common units from each set.
	 * We don't use array_diff because we want (for eaxmple) mm*mm/mm*cm to
	 * end up as mm/cm. 
	 * @param array first set of units
	 * @param array second set of units
	 * @return array both sets of units with common units removed
	 */
	private function removeCommonUnits($u1, $u2) {
		$_u1 = array();
		while (!empty($u1)) {
			$u = array_shift($u1);
			$i = array_search($u, $u2);
			if ($i !== false) {
				unset($u2[$i]);
			}
			else {
				$_u1[] = $u;
			}
		}
		return (array($_u1, $u2));
	}

	/**
	 * Returns a value indicating if this number is unitless.
	 * @return boolean true if this number is unitless, false if not
	 */
	public function isUnitless() {
	  return empty($this->numeratorUnits) && empty($this->denominatorUnits);
	}

	/**
	 * Returns a value indicating if this number has units that can be represented
	 * in CSS.
	 * @return boolean true if this number has units that can be represented in
	 * CSS, false if not
	 */
	public function hasLegalUnits() {
	  return (empty($this->numeratorUnits) || count($this->numeratorUnits) === 1) &&
	  	empty($this->denominatorUnits);
	}

	/**
	 * Returns a string representation of the units.
	 * @return string the units
	 */
	public function unitString($numeratorUnits, $denominatorUnits) {
	  return join(' * ', $numeratorUnits) .
	  	(!empty($denominatorUnits) ? ' / ' . join(' * ', $denominatorUnits) : '');
	}

	/**
	 * Returns the units of this number.
	 * @return string the units of this number
	 */
	public function getUnits() {
	  return $this->unitString($this->numeratorUnits, $this->denominatorUnits);
	}

	/**
	 * Returns the denominator units of this number.
	 * @return string the denominator units of this number
	 */
	public function getDenominatorUnits() {
	  return join(' * ', $this->denominatorUnits);
	}

	/**
	 * Returns the numerator units of this number.
	 * @return string the numerator units of this number
	 */
	public function getNumeratorUnits() {
	  return join(' * ', $this->numeratorUnits);
	}
	
	/**
	 * Returns a value indicating if this number can be compared to other.
	 * @return boolean true if this number can be compared to other, false if not
	 */
	public function isComparableTo($other) {
		try {
			$this->op_plus($other);
			return true; 
		}
		catch (Exception $e) {
			return false; 
		}
	}

	/**
	 * Returns a value indicating if this number is an integer.
	 * @return boolean true if this number is an integer, false if not
	 */
	public function isInt() {
	  return $this->value % 1 === 0;
	}

	/**
	 * Returns the value of this number.
	 * @return float the value of this number.
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Returns the integer value.
 	 * @return integer the integer value.
 	 * @throws SassNumberException if the number is not an integer
	 */
	public function toInt() {
		if  (!$this->isInt()) {
			throw new SassNumberException('Not an integer: {value}', array('{value}'=>$this->value), SassScriptParser::$context->node);
		}
	  return intval($this->value);
	}

	/**
	 * Converts the number to a string with it's units if any.
	 * If the units are px the result is rounded down to the nearest integer,
	 * otherwise the result is rounded to the specified precision.
 	 * @return string number as a string with it's units if any
	 */
	public function toString() {
		if  (!$this->hasLegalUnits()) {
			throw new SassNumberException('Invalid {what}', array('{what}'=>"CSS units ({$this->units})"), SassScriptParser::$context->node);
		}
	  return ($this->units == 'px' ? floor($this->value) :
	  		round($this->value, self::PRECISION)).$this->units;
	}

	/**
	 * Returns a value indicating if a token of this type can be matched at
	 * the start of the subject string.
	 * @param string the subject string
	 * @return mixed match at the start of the string or false if no match
	 */
	public static function isa($subject) {
		return (preg_match(self::MATCH, $subject, $matches) ? $matches[0] : false);
	}
}
