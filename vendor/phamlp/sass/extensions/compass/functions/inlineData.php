<?php
/* SVN FILE: $Id: SassBoolean.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * Compass extension SassScript inline data class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.extensions.compass.functions
 */
 
/**
 * Compass extension SassScript inline data functions class.
 * A collection of functions for use in SassSCript.
 * @package			PHamlP
 * @subpackage	Sass.extensions.compass.functions
 */
class SassExtentionsCompassFunctionsInlineData {
	public function inline_image($path, $mime_type = null) {
		$path = $path->value;
		$real_path = SassExtentionsCompassConfig::config('images_path').DIRECTORY_SEPARATOR.$path;
		$url = 'url(data:'.self::compute_mime_type($path, $mime_type).';base64,'.self::data($real_path).')';
		return new SassString($url);
	}

	public function inline_font_files() {
		if (func_num_args() % 2)
			throw new SassScriptFunctionException('An even number of arguments must be passed to inline_font_files()', array(), SassScriptParser::$context->node);

		$args = func_get_args();
		$files = array();
		while ($args) {
			$path = array_shift($args);
			$real_path = SassExtentionsCompassConfig::config('fonts_path').DIRECTORY_SEPARATOR.$path->value;
			$fp = fopen($real_path, 'rb');
			$url = 'url(data:'.self::compute_mime_type($path).';base64,'.self::data($real_path).')';
			$files[] = "$url format('".array_shift($args)."')";
		}
		return new SassString(join(", ", $files));
	}

	private function compute_mime_type($path, $mime_type = null) {
		if ($mime_type) return $mime_type;
		
		switch (true) {
			case preg_match('/\.png$/i', $path):
				return 'image/png';
				break;
			case preg_match('/\.jpe?g$/i', $path):
				return 'image/jpeg';
				break;
			case preg_match('/\.gif$/i', $path):
				return 'image/gif';
				break;
			case preg_match('/\.otf$/i', $path):
				return 'font/opentype';
				break;
			case preg_match('/\.ttf$/i', $path):
				return 'font/truetype';
				break;
			case preg_match(' /\.woff$/i', $path):
				return 'font/woff';
				break;
			case preg_match(' /\.off$/i', $path):
				return 'font/openfont';
				break;
			case preg_match('/\.([a-zA-Z]+)$/i', $path, $matches):
				return 'image/'.strtolower($matches[1]);
				break;
			default:
				throw new SassScriptFunctionException('Unable to determine mime type for {what}, please specify one explicitly', array('{what}'=>$path), SassScriptParser::$context->node);
				break;
		}
	}

	private function data($real_path) {
		if (file_exists($real_path)) {
			$fp = fopen($real_path, 'rb');
			return base64_encode(fread($fp, filesize($real_path)));
		}
		else
			throw new SassScriptFunctionException('Unable to find {what}: {filename}', array('{what}'=>'file', '{filename}'=>$real_path), SassScriptParser::$context->node);
	}
}
