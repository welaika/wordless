<?php
/* SVN FILE: $Id$ */
/**
 * Markdown Filter for {@link http://haml-lang.com/ Haml} the 
 * {@link http://www.yiiframework.com/ Yii PHP framework} for use with
 * {@link http://phamlp.googlecode.com PHamlP}.
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
 
Yii::import('ext.phamlp.vendors.phamlp.haml.filters._HamlMarkdownFilter');

/**
 * Markdown Filter for {@link http://haml-lang.com/ Haml} class.
 * Parses the text with Markdown.
 * @package			PHamlP
 * @subpackage	Yii.filters
 */
class HamlMarkdownFilter extends _HamlMarkdownFilter {
	/**
	 * Initialise the filter with the $vendorClass
	 */	
	public function init() {
		$this->vendorClass='CMarkdownParser';
		parent::init();
	}
}