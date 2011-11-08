<?php
/* SVN FILE: $Id: SassBoolean.php 49 2010-04-04 10:51:24Z chris.l.yates $ */
/**
 * Compass extension SassScript urls functions class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.extensions.compass.functions
 */
 
/**
 * Compass extension SassScript urls functions class.
 * A collection of functions for use in SassSCript.
 * @package			PHamlP
 * @subpackage	Sass.extensions.compass.functions
 */
class SassExtentionsCompassFunctionsUrls {
	public function stylesheet_url($path, $only_path = null) {
		$path = $path->value; # get to the string value of the literal.
		
		# Compute the $path to the stylesheet, either root relative or stylesheet relative
		# or nil if the http_images_path is not set in the configuration.
		if (SassExtentionsCompassConfig::config('relative_assets'))
			$http_css_path = self::compute_relative_path(SassExtentionsCompassConfig::config('css_path'));
		elseif (SassExtentionsCompassConfig::config('http_css_path'))
			$http_css_path = SassExtentionsCompassConfig::config('http_css_path');
		else
			$http_css_path = SassExtentionsCompassConfig::config('css_dir');

		return new SassString(self::clean("$http_css_path/$path", $only_path));
	}

	public function font_url($path, $only_path = null) {
		$path = $path->value; # get to the string value of the literal.

		# Short circuit if they have provided an absolute url.
		if (self::is_absolute_path($path)) {
			return new SassString("url('$path')");
		}

		# Compute the $path to the font file, either root relative or stylesheet relative
		# or nil if the http_fonts_path cannot be determined from the configuration.
		if (SassExtentionsCompassConfig::config('relative_assets'))
			$http_fonts_path = self::compute_relative_path(SassExtentionsCompassConfig::config('fonts_path'));
		else
			$http_fonts_path = SassExtentionsCompassConfig::config('http_fonts_path');

		return new SassString(self::clean("$http_fonts_path/$path", $only_path));
	}

	public function image_url($path, $only_path = null) {
		$path = $path->value; # get to the string value of the literal.

		if (preg_match('%^'.preg_quote(SassExtentionsCompassConfig::config('http_images_path'), '%').'/(.*)%',$path, $matches))
			# Treat root relative urls (without a protocol) like normal if they start with
			# the images $path.
			$path = $matches[1];
		elseif (self::is_absolute_path($path))
			# Short curcuit if they have provided an absolute url.
			return new SassString("url('$path')");

		# Compute the $path to the image, either root relative or stylesheet relative
		# or nil if the http_images_path is not set in the configuration.
		if (SassExtentionsCompassConfig::config('relative_assets'))
			$http_images_path = self::compute_relative_path(SassExtentionsCompassConfig::config('images_path'));
		elseif (SassExtentionsCompassConfig::config('http_images_path'))
			$http_images_path = SassExtentionsCompassConfig::config('http_images_path');
		else
			$http_images_path = SassExtentionsCompassConfig::config('images_dir');

		# Compute the real $path to the image on the file stystem if the images_dir is set.
		if (SassExtentionsCompassConfig::config('images_dir'))
			$real_path = SassExtentionsCompassConfig::config('project_path').
				DIRECTORY_SEPARATOR.SassExtentionsCompassConfig::config('images_dir').
				DIRECTORY_SEPARATOR.$path;

		# prepend the $path to the image if there's one
		if ($http_images_path) {
			$http_images_path .= (substr($http_images_path, -1) === '/' ? '' : '/');
			$path = $http_images_path.$path;
		}

/*		# Compute the asset host unless in relative mode.
		asset_host = if !(self::relative()) && Compass.configuration.asset_host
			Compass.configuration.asset_host.call($path)
		}

		# Compute and append the cache buster if there is one.
		if buster = compute_cache_buster($path, real_path)
			$path += "?#{buster}"
		}

		# prepend the asset host if there is one.
		$path = "#{asset_host}#{'/' unless $path[0..0] == "/"}#{$path}" if asset_host*/

		return new SassString(self::clean($path, $only_path));
	}
	
	# takes off any leading "./".
	# if $only_path emits a $path, else emits a url
	private function clean($url, $only_path) {
		if (!$only_path instanceof SassBoolean) {
			$only_path = new SassBoolean('false');
		}
		
		$url = (substr($url, 0, 2) === './' ? substr($url, 2) : $url);		
		return ($only_path->toBoolean() ? $url : "url('$url')");
	}

	private function is_absolute_path($path) {
		return ($path[0] === '/' || substr($path, 0, 4) === 'http');
	}

	// returns the path relative to the target css file
	private function compute_relative_path($path) {
		return $path;
/*		if (target_css_file = options[:css_filename]) {
			Pathname.new($path).relative_path_from(Pathname.new(File.dirname(target_css_file))).to_s
		}*/
	}

/*	private function compute_cache_buster($path, real_path) {
		if Compass.configuration.asset_cache_buster {
			args = [$path]
			if Compass.configuration.asset_cache_buster.arity > 1 {
				args << (File.new(real_path) if real_path)
			}
			Compass.configuration.asset_cache_buster.call(*args)
		elseif real_path {
			default_cache_buster($path, real_path)
		}
	}

	private function default_cache_buster($path, real_path) {
		if File.readable?(real_path) {
			File.mtime(real_path).to_i.to_s
		}
		else {
			$stderr.puts "WARNING: '#{File.basename($path)}' was not found (or cannot be read) in #{File.dirname(real_path)}"
		}
	}	*/
}