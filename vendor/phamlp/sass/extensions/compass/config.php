<?php
/* SVN FILE: $Id: SassBoolean.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * Compass extension configuration class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.extensions.compass
 */
 
/**
 * Compass extension configuration class.
 * @package			PHamlP
 * @subpackage	Sass.extensions.compass
 */
class SassExtentionsCompassConfig {
	public static $config;
	private static $defaultConfig = array(
						 'project_path' => '',
								'http_path' => '/',
									'css_dir' => 'css',
								 'css_path' => '',
						'http_css_path' => '',
								'fonts_dir' => 'fonts',
							 'fonts_path' => '',
					'http_fonts_path' => '',
							 'images_dir' => 'images',
							'images_path' => '',
				 'http_images_path' => '',
					'javascripts_dir' => 'javascripts',
				 'javascripts_path' => '',
		'http_javascripts_path' => '',
					'relative_assets' => true,
	);
	
	/**
	 * Sets configuration settings or returns a configuration setting. 
	 * @param mixed array: configuration settings; string: configuration setting to return
	 * @return string configuration setting. Null if setting does not exist.
	 */
	public function config($config) {
		if (is_array($config)) {
			self::$config = array_merge(self::$defaultConfig, $config);
			self::setDefaults();
		}			
		elseif (is_string($config) && isset(self::$config[$config])) {
			return self::$config[$config]; 
		}	
	}
	
	/**
	 * Sets default values for paths not specified 
	 */
	private static function setDefaults() {
		foreach (array('css', 'images', 'fonts', 'javascripts') as $asset) {
			if (empty(self::$config[$asset.'_path'])) {
				self::$config[$asset.'_path'] = self::$config['project_path'].DIRECTORY_SEPARATOR.self::$config[$asset.'_dir'];
			}
			if (empty(self::$config['http_'.$asset.'_path'])) {
				self::$config['http_'.$asset.'_path'] = self::$config['http_path'].self::$config[$asset.'_dir'];
			}
		}
	}
}