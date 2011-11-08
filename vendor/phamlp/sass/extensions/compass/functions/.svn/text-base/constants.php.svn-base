<?php
/* SVN FILE: $Id: SassBoolean.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * Compass extension SassScript constants functions class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.extensions.compass.functions
 */
 
/**
 * Compass extension SassScript constants functions class.
 * A collection of functions for use in SassSCript.
 * @package			PHamlP
 * @subpackage	Sass.extensions.compass.functions
 */
class SassExtentionsCompassFunctionsConstants {
	public static function opposite_position($pos) {
		$opposites = array();
		foreach (explode(' ', $pos->toString()) as $position) {
			switch (trim($position)) {
				case 'top':
					$opposites[] = 'bottom';
					break;
				case 'right':
					$opposites[] = 'left';
					break;
				case 'bottom':
					$opposites[] = 'top';
					break;
				case 'left':
					$opposites[] = 'right';
					break;
				case 'center':
					$opposites[] = 'center';
					break;
				default:
					throw new Exception('Cannot determine the opposite of '.trim($position));
			}
		}
		return new SassString(join(' ', $opposites));
	}
}