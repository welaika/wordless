<?php

/*!
 * Detector Helpers v0.1
 *
 * Features that can be used to add extra functionality to your page but aren't required
 *
 * Copyright (c) 2011-2012 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 */

class DetectorHelpers {
	
	/**
	* Adds classes to the HTML tag if necessary
	* @param  {Object}        the user agent features
	* @param  {String}        list of browser features to include in the css, bad idea to leave features blank...
	*/
	public static function createHTMLList($obj,$features = null,$printUAProps = false) {
		if ($features != null) {
			$features_a = explode(",",$features);
			array_walk($features_a,create_function('&$val', '$val = trim($val);'));
		}
		foreach($obj as $key=>$value) {
			if (is_object($value)) {
				foreach ($value as $vkey => $vvalue) {
					$vkey = $key."-".$vkey;
					if (!$features || in_array($vkey,$features_a)) {
						$result = ($vvalue) ? $vkey : 'no-'.$vkey;
						print $result.' ';
					}
				}
			} else {
				if (!$features || in_array($key,$features_a)) {
					$result = ($value) ? $key : 'no-'.$key;
					print $result.' ';
				}
			}
		}
		if ($printUAProps) {
			$uaProps = array("os","osFull","browserFull","device","deviceFull");
			foreach($uaProps as $uaProp) {
				print str_replace(" ","-", strtolower($obj->$uaProp)).' ';
			}
		}
	}

	/**
	* Adds a JavaScript object to the page so features collected on the server can be used client-side
	* @param  {Object}        the user agent features
	* @param  {String}        list of browser features to include in the css, bad idea to leave features blank...
	*/
	public static function createJavaScriptObj($obj,$features = null) {
		print "<script type=\"text/javascript\">";
		print "Detector=new Object();";
		if ($features) {
			$features_a = explode(",",$features);
			array_walk($features_a,create_function('&$val', '$val = trim($val);'));
		}
		foreach($obj as $key=>$value) {
			if (is_object($value)) {
				$i = 0;
				foreach ($value as $vkey => $vvalue) {
					if (!$features || in_array($key."-".$vkey,$features_a)) {
						if ($i == 0) {
							print "Detector.".$key."=new Object();\n";
							$i++;
						}
						$vkey = str_replace("-","",$vkey);
						if ($vvalue) {
							print "Detector.".$key.".".$vkey."=true;\n";
						} else {
							print "Detector.".$key.".".$vkey."=false;\n";
						}
					
					}
				}
			} else {
				if (!$features || in_array($key,$features_a)) {
					$key = str_replace("-","",$key);
					if ($value === true) {
						print "Detector.".$key."=true;\n";
					} else if ($value == false) {
						print "Detector.".$key."=false;\n";
					} else {
						print "Detector.".$key."='".$value."';\n";
					}
				}
			}
		}
		print "</script>";
	}
}

?>