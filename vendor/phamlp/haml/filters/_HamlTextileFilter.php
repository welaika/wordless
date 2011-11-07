<?php
/* SVN FILE: $Id: _HamlTextileFilter.php 51 2010-04-14 12:05:03Z chris.l.yates $ */
/**
 * Textile Filter for {@link http://haml-lang.com/ Haml} class file.
 * This filter is an abstract filter that must be extended. 
 * 
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright		Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml.filters
 */

/**
 * Textile Filter for {@link http://haml-lang.com/ Haml} class.
 * Parses the text with Textile.
 * 
 * This is an abstract class that must be extended and the init() method
 * implemented to provide the vendorPath if the vendor class is not imported
 * elsewhere in the application (e.g. by a framework) and vendorClass if the
 * default class name is not correct.
 * @package			PHamlP
 * @subpackage	Haml.filters
 */
abstract class _HamlTextileFilter extends HamlBaseFilter {
	/**
	 * @var string Path to Textile Parser
	 */
	protected $vendorPath;
	/**
	 * @var string Textile class
	 * Override this value if the class name is different in your environment
	 */
	protected $vendorClass = 'Textile';
	
	/**
	 * Child classes must implement this method.
	 * Typically the child class will set $vendorPath and $vendorClass
	 */
	public function init() {}

	/**
	 * Run the filter
	 * @param string text to filter
	 * @return string filtered text
	 */
	public function run($text) {
		return '<?php	'.(!empty($this->vendorPath)?'require_once "'.$this->vendorPath.'";':'').'$textile___=new '.$this->vendorClass.'();echo  $textile___->TextileThis("'.preg_replace(HamlParser::MATCH_INTERPOLATION, '".\1."', $text).'");?>';
	}
}