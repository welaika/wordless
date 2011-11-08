<?php
/* SVN FILE: $Id$ */
/**
 * Textile Filter for {@link http://haml-lang.com/ Haml} the 
 * {@link http://cakephp.org/ Yii CakePHP framework} for use with
 * {@link http://phamlp.googlecode.com PHamlP}.
 * 
 * Requires {@link http://textile.thresholdstate.com/ Textile} to be installed
 * in your APP.vendors.textile directory.
 * 
 * This file should be placed in the filterDir directory as defined in the
 * HamlParser options.
 * 
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright		Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Cake.filters
 */

// Change this to the required path
define('VENDOR_PATH', APP.'vendors'.DS.'textile'.DS.'classTextile.php');

// The base filter class
App::import('Vendor', 'HamlTextileFilter', array('file'=>'phamlp'.DS.'haml'.DS.'filters'.DS.'_HamlTextileFilter.php'));

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
		$this->vendorPath = VENDOR_PATH;		
		parent::init();
	}
}