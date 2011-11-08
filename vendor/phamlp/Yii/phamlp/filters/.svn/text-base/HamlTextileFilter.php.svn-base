<?php
/* SVN FILE: $Id$ */
/**
 * Textile Filter for {@link http://haml-lang.com/ Haml} the 
 * {@link http://www.yiiframework.com/ Yii PHP framework} for use with
 * {@link http://phamlp.googlecode.com PHamlP}.
 * 
 * Requires {@link http://textile.thresholdstate.com/ Textile} to be installed.
 * Default installion is in 'application.vendors.textile'
 * 
 * This file should be placed in the filterDir directory as defined in the
 * HamlParser options.
 * 
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright		Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Yii.filters
 */

// Change this to the required path alias
define('VENDOR_PATH', 'application.vendors.textile.classTextile');

// The base filter class
Yii::import('ext.phamlp.vendors.phamlp.haml.filters._HamlTextileFilter');

/**
 * Textile Filter for {@link http://haml-lang.com/ Haml} class.
 * Parses the text with Textile.
 * @package			PHamlP
 * @subpackage	Yii.filters
 */
class HamlTextileFilter extends _HamlTextileFilter {
	/**
	 * Initialise the filter with the $vendorPath
	 */
	public function init() {
		$this->vendorPath = Yii::getPathOfAlias(VENDOR_PATH);		
		parent::init();
	}
}