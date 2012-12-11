<?php

/*!
 * Detector v0.8.5
 *
 * Copyright (c) 2011-2012 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 */

// address 5.2 compatibility
if (!defined('__DIR__')) define('__DIR__', dirname(__FILE__));
if (!function_exists('json_decode') || !function_exists('json_encode')) {
	require_once(__DIR__."/lib/json/jsonwrapper.php");
}

class Detector {
	
	private static $debug               = false; // gets overwritten by the config so changing this won't do anything for you...
	
	public static  $ua;
	public static  $accept;
	
	private static $coreVersion;
	private static $extendedVersion;
	
	public static  $foundIn;             // this is just for the demo. won't ever really be needed i don't think
	
	private static $uaHash;
	private static $sessionID;
	private static $cookieID;
	private static $uaFeaturesMaxJS;     // all the default Modernizr Tests
	private static $uaFeaturesMinJS;     // NO default tests except media queries, meant to run those in the perrequest folder
	private static $uaFeaturesCore; 
	private static $uaFeaturesExtended;
	private static $uaFeaturesPerSession;
	private static $uaFeaturesPerRequest;
	
	private static $uaDirCore;
	private static $uaDirExtended;
	
	private static $featuresScriptWebPath;
	
	public static $defaultFamily;
	public static $switchFamily;
	public static $splitFamily;
	public static $noJSCookieFamilySupport;
	public static $noJSSearchFamily;
	public static $noJSDefaultFamily;
	public static $noCookieFamily;
	
	/**
	* Configures the shared variables in Detector so that they can be used in functions that might not need to run Detector::build();
	*
	* @return {Boolean}       the result of checking the current user agent string against a list of bots
	*/
	private static function configure() {
		
		// set-up the configuration options for the system
		if (!($config = @parse_ini_file(__DIR__."/config/config.ini"))) {
			// config.ini didn't exist so attempt to create it using the default file
			if (!@copy(__DIR__."/config/config.ini.default", __DIR__."/config/config.ini")) {
			    print "Please make sure config.ini.default exists before trying to have Detector build the config.ini file automagically.";
				exit;
			} else {
				$config = @parse_ini_file(__DIR__."/config/config.ini");	
			}
		}
		
		// populate some standard variables out of the config
		foreach ($config as $key => $value) {
			self::$$key = $value;
		}
		
		// populate some standard variables based on the user agent string
		self::$ua                   = strip_tags($_SERVER["HTTP_USER_AGENT"]);
		self::$accept               = strip_tags($_SERVER["HTTP_ACCEPT"]);
		self::$uaHash               = md5(self::$ua);
		self::$sessionID            = md5(self::$ua."-session-".self::$coreVersion."-".self::$extendedVersion);
		self::$cookieID             = md5(self::$ua."-cookie-".self::$coreVersion."-".self::$extendedVersion);
		
	}
	
	/**
	* Tests to see if:
	*     - see if this is a debug request with appropriately formed pid, else
	*     - see if the cookie for the per user test has been set so we can record the results and add to the session
	*     - see if a session has already been opened for the request browser, if so send the info back, else
	*     - see if the cookie for the full test has been set so we can build the profile, if so build the profile & send the info back, else
	*     - see if this browser reports being a spider, doesn't support JS or doesn't support cookies
	*     - see if detector can find an already created profile for the browser, if so send the info back, else
	*     - start the process for building a profile for this unknown browser
	*
	* Logic is based heavily on modernizr-server
	*
	* @return {Object}       an object that contains all the properties for this particular user agent
	*/
	public static function build() {
		
		// configure detector from config.ini
		self::configure();
		
		// populate some variables specific to build()
		$uaFileCore                 = __DIR__."/".self::$uaDirCore.self::uaDir()."ua.".self::$uaHash.".json";
		$uaFileExtended             = __DIR__."/".self::$uaDirExtended.self::uaDir()."ua.".self::$uaHash.".json";
		
		$uaTemplateCore             = __DIR__."/".self::$uaDirCore."ua.template.json";
		$uaTemplateExtended         = __DIR__."/".self::$uaDirExtended."ua.template.json";
		
		$pid                        = (isset($_REQUEST['pid']) && preg_match("/[a-z0-9]{32}/",$_REQUEST['pid'])) ? $_REQUEST['pid'] : false;
		
		// offer the ability to review profiles saved in the system
		if ($pid && self::$debug) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "archive";
			
			// decode the core data
			$uaJSONCore     = json_decode(@file_get_contents(__DIR__."/".self::$uaDirCore.self::uaDir($pid)."ua.".$pid.".json"));
			
			// find and decode the extended data
			$uaJSONExtended = json_decode(@file_get_contents(__DIR__."/".self::$uaDirExtended.self::uaDir($pid)."ua.".$pid.".json"));
			
			// merge the data
			$mergedInfo = ($uaJSONExtended) ? (object) array_merge((array) $uaJSONCore, (array) $uaJSONExtended) : $uaJSONCore;
			
			// some general properties
			$mergedInfo->nojs      = false;
			$mergedInfo->nocookies = false;
			
			// put the merged JSON info into session
			if (isset($_SESSION)) {
				$_SESSION[self::$sessionID] = $mergedInfo;
			}
			
			// return to the script
			return $mergedInfo;
		
		} else if (@session_start() && isset($_SESSION) && isset($_SESSION[self::$sessionID]) && isset($_COOKIE) && isset($_COOKIE[self::$cookieID."-ps"])) {

			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "persession";

			// parse the per request cookie
			$cookiePerSession = new stdClass();
			$cookiePerSession = self::parseCookie("ps",$cookiePerSession,true);

			// parse the per request cookie
			$cookiePerRequest = new stdClass();
			$cookiePerRequest = self::parseCookie("pr",$cookiePerRequest,true);

			// merge the session info we already have and the info from the cookie
			$mergedInfo = (isset($cookiePerSession)) ? (object) array_merge((array) $_SESSION[self::$sessionID], (array) $cookiePerSession) : $_SESSION[self::$sessionID];
			$mergedInfo = (isset($cookiePerRequest)) ? (object) array_merge((array) $mergedInfo, (array) $cookiePerRequest) : $mergedInfo;

			// unset the cookies
			setcookie(self::$cookieID,"");
			setcookie(self::$cookieID."-ps","");

			// put the merged JSON info into session
			if (isset($_SESSION)) {
				$_SESSION[self::$sessionID] = $mergedInfo;
			}
			
			// send the data back to the script to be used
			return $mergedInfo;

		} else if (@session_start() && isset($_SESSION) && isset($_SESSION[self::$sessionID])) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "session";
			
			// parse the per request cookie
			$cookiePerRequest = new stdClass();
			$cookiePerRequest = self::parseCookie("pr",$cookiePerRequest);
			
			// merge the session info we already have and the info from the cookie
			$mergedInfo = (isset($cookiePerRequest)) ? (object) array_merge((array) $_SESSION[self::$sessionID], (array) $cookiePerRequest) : $_SESSION[self::$sessionID];
			
			// put the merged JSON info into session
			if (isset($_SESSION)) {
				$_SESSION[self::$sessionID] = $mergedInfo;
			}

			// send the data back to the script to be used
			return $mergedInfo;
			
		} else if (($uaJSONCore = json_decode(@file_get_contents($uaFileCore))) && ($uaJSONExtended = json_decode(@file_get_contents($uaFileExtended)))) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "file";
			
			// double-check that the already created profile matches the current version of the core & extended templates
			if (($uaJSONCore->coreVersion != self::$coreVersion) || ($uaJSONExtended->extendedVersion != self::$extendedVersion)) {
				self::buildTestPage();	
			}
				
			// merge the data
			$mergedInfo = ($uaJSONExtended) ? (object) array_merge((array) $uaJSONCore, (array) $uaJSONExtended) : $uaJSONCore;	

			// some general properties
			$mergedInfo->nojs      = false;
			$mergedInfo->nocookies = false;
			
			// put the merged JSON info into session
			if (isset($_SESSION)) {
				$_SESSION[self::$sessionID] = $mergedInfo;
			}

			// need to build the tests for the per user
			if (self::readDirFiles(self::$uaFeaturesPerSession, true)) {		
				self::persession();
			}
				
			// return to the script
			return $mergedInfo;
		
		}  else if (self::checkSpider() || (isset($_REQUEST["nojs"]) && ($_REQUEST["nojs"] == "true")) || (isset($_REQUEST["nocookies"]) && ($_REQUEST["nocookies"] == "true"))) {
			
			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "nojs";

			// open the JSON template core & extended files that will be populated
			$jsonTemplateCore     = self::openUAFile($uaTemplateCore);
			$jsonTemplateExtended = self::openUAFile($uaTemplateExtended);
			
			// use ua-parser-php to set-up the basic properties for this UA, populate other core properties
			// include the basic properties of the UA
			$jsonTemplateCore->ua          = self::$ua;
			$jsonTemplateCore->uaHash      = self::$uaHash;
			$jsonTemplateCore->coreVersion = self::$coreVersion;
			$jsonTemplateCore              = self::createUAProperties($jsonTemplateCore);
			
			// populate extended properties
			$jsonTemplateExtended                  = !isset($jsonTemplateExtended) ? new stdClass() : $jsonTemplateExtended;
			$jsonTemplateExtended->ua              = self::$ua;
			$jsonTemplateExtended->uaHash          = self::$uaHash;
			$jsonTemplateExtended->extendedVersion = self::$extendedVersion;

			$mergedInfo = new stdClass();
			$mergedInfo = (object) array_merge((array) $jsonTemplateCore, (array) $jsonTemplateExtended);
			
			// some general properties
			$mergedInfo->nojs      = false;
			$mergedInfo->nocookies = false;
			
			// add an attribute to the object in case no js or no cookies was sent
			if (isset($_REQUEST["nojs"]) && ($_REQUEST["nojs"] == "true")) {
				$mergedInfo->nojs      = true;
			} else if (isset($_REQUEST["nocookies"]) && ($_REQUEST["nocookies"] == "true")) {
				$mergedInfo->nocookies = true;
			} 

			// try setting the session unless cookies are actively not supported
			if (!(isset($_REQUEST["nocookies"]) && ($_REQUEST["nocookies"] == "true")) && isset($_SESSION)) {
				$_SESSION[self::$sessionID] = $mergedInfo;
			}
			
			// return the collected data to the script for use in this go around
			return $mergedInfo;
		
		} else if (isset($_COOKIE) && isset($_COOKIE[self::$cookieID])) {

			// to be clear, this section means that a UA was unknown, was profiled with modernizr & now we're saving that data to build a new profile

			// where did we find this info to display... probably only need this for the demo
			self::$foundIn = "cookie";

			// open the JSON template core & extended files that will be populated
			$jsonTemplateCore     = self::openUAFile($uaTemplateCore);
			$jsonTemplateExtended = self::openUAFile($uaTemplateExtended);

			// use ua-parser-php to set-up the basic properties for this UA, populate other core properties
			$jsonTemplateCore->ua          = self::$ua;
			$jsonTemplateCore->uaHash      = self::$uaHash;
			$jsonTemplateCore->coreVersion = self::$coreVersion;
			$jsonTemplateCore              = self::createUAProperties($jsonTemplateCore);

			// populate extended properties
			$jsonTemplateExtended                  = !isset($jsonTemplateExtended) ? new stdClass() : $jsonTemplateExtended;
			$jsonTemplateExtended->ua              = self::$ua;
			$jsonTemplateExtended->uaHash          = self::$uaHash;
			$jsonTemplateExtended->extendedVersion = self::$extendedVersion;

			// create objects to hold any of the per user or per request data. it shouldn't be saved to file but it should be added to the session
			$cookiePerSession = new stdClass();
			$cookiePerRequest = new stdClass();

			// push features into the same level as the general device information
			// change 1/0 to true/false. why? 'cause that's what i like to read ;)
			$jsonTemplateCore     = self::parseCookie("core",$jsonTemplateCore,true);
			$jsonTemplateExtended = self::parseCookie("extended",$jsonTemplateExtended,true);
			$cookiePerSession     = self::parseCookie("ps",$cookiePerSession,true);
			$cookiePerRequest     = self::parseCookie("pr",$cookiePerRequest,true);

			// merge the data for future requests
			$mergedInfo = new stdClass();
			$mergedInfo = ($jsonTemplateExtended) ? (object) array_merge((array) $jsonTemplateCore, (array) $jsonTemplateExtended) : $jsonTemplateCore;
			$mergedInfo = ($cookiePerSession) ? (object) array_merge((array) $mergedInfo, (array) $cookiePerSession) : $mergedInfo;
			$mergedInfo = ($cookiePerRequest) ? (object) array_merge((array) $mergedInfo, (array) $cookiePerRequest) : $mergedInfo;

			// some general properties
			$mergedInfo->nojs      = false;
			$mergedInfo->nocookies = false;

			// write out to disk for future requests that might have the same UA
			self::writeUAFile(json_encode($jsonTemplateCore),$uaFileCore);
			self::writeUAFile(json_encode($jsonTemplateExtended),$uaFileExtended);

			// add the user agent & hash to a list of already saved user agents
			// not needed. a performance hit. really only necessary for detector.dmolsen.com
			// self::addToUAList();

			// unset the cookie that held the vast amount of test data
			setcookie(self::$cookieID,"");

			// add our collected data to the session for use in future requests, also add the per request data
			if (isset($_SESSION)) {
				$_SESSION[self::$sessionID] = $mergedInfo;
			}

			// return the collected data to the script for use in this go around
			return $mergedInfo;

		} else {
			
			// didn't recognize that the user had been here before nor the UA string.
			self::buildTestPage();

		}
	}
	
	/**
	* Reads in the per request feature tests and sends them to the function that builds out the JS & cookie
	*
	* from modernizr-server
	*
	* @return {String}       the HTML & JavaScript that tracks the per request test
	*/
	public static function perrequest() {
		self::configure();
		if ((isset($_REQUEST['dynamic']) && ($_REQUEST['dynamic'] == "true")) && !(isset($_REQUEST["nocookies"]) && ($_REQUEST["nocookies"] == "true"))) {
			readfile(__DIR__ . '/lib/modernizr/cookieTest.js');
		}
		readfile(__DIR__ . '/' . self::$uaFeaturesMinJS);
		self::readDirFiles(self::$uaFeaturesPerRequest);
		print self::_mer(false,'-pr');
	}
	
	/**
	* Reads in the per session & per request feature tests and sends them to the function that builds out the JS & cookie. forces a reload
	*
	* from modernizr-server
	*
	* @return {String}       the HTML & JavaScript that tracks the per user & per request test
	*/
	public static function persession() {
		// gather info by sending Modernizr & custom tests
		print "<!DOCTYPE html><html lang=\"en\"><head><meta name=\"viewport\" content=\"width=device-width\"><script type='text/javascript'>";
		print "document.cookie='".self::$cookieID."-ps=foo;path=/';"; // hack around how the cookies get handled in general
		readfile(__DIR__ . '/lib/modernizr/cookieTest.js');
		readfile(__DIR__ . '/' . self::$uaFeaturesMinJS);
		self::readDirFiles(self::$uaFeaturesPerSession);
		self::readDirFiles(self::$uaFeaturesPerRequest);
		print self::_mer() . "</script></head><body><noscript><meta http-equiv='refresh' content='0; url=".self::buildNoscriptLink()."'></noscript></body></html>";
		exit;
	}
	
	/**
	* Builds the browser test page
	*/
	public static function buildTestPage() {		
		// gather info by sending Modernizr & custom tests
		print "<!DOCTYPE html><html lang=\"en\"><head><meta name=\"viewport\" content=\"width=device-width\"><script type='text/javascript'>";
		readfile(__DIR__ . '/lib/modernizr/cookieTest.js');
		readfile(__DIR__ . '/' . self::$uaFeaturesMaxJS);
		self::readDirFiles(self::$uaFeaturesCore);
		self::readDirFiles(self::$uaFeaturesExtended);
		self::readDirFiles(self::$uaFeaturesPerSession);
		self::readDirFiles(self::$uaFeaturesPerRequest);
		print self::_mer() . "</script></head><body><noscript><meta http-equiv='refresh' content='0; url=".self::buildNoscriptLink()."'></noscript></body></html>";
		exit;
		
	}
	
	/**
	* Creates the JavaScript & cookie that tracks the features for a particular browser
	* @param  {Boolean}      if the javascript should include a page reload statement
	* @param  {String}       if the cookie that is created should have a string appended to it. used for per request tests.
	*
	* from modernizr-server
	*
	* @return {String}       the HTML & JavaScript that tracks the per request test
	*/
	private static function _mer($reload = true, $cookieExtra = '') {
		$output = "".
		  "var m=Modernizr;var c='';var k=''; var f;".
		  "for(f in m){".
		    "var j='';".
		    "if(f[0]=='_'){continue;}".
		    "var t=typeof m[f];".
		    "if(t=='function'){continue;}".
		    "c+=(c?'|':'".self::$cookieID.$cookieExtra."=')+f+':';".
		    "var kt=(f.slice(0,3)=='pr-')?true:false;".
		    "if(kt){k+=(k?'|':'".self::$cookieID."-pr=')+f+':';}".
		    "if(t=='object'){".
			  "var s;".
		      "for(s in m[f]){".
				"if (typeof m[f][s]=='boolean') { j+='/'+s+':'+(m[f][s]?1:0); }".
		        "else { j+='/'+s+':'+m[f][s]; }".
		      "}".
			  "c+=j;".
		      "k+=kt?j:'';".
		    "}else{".
		      "j=m[f]?'1':'0';".
		      "c+=j;".
		      "k+=kt?j:'';".
		    "}".
		  "}".
		  "c+=';path=/';".
		  "if(k){k+=';path=/';}".
		  "try{".
		    "if (getCookie() != 'testData') {".
				"window.location = cookieRedirect; ".
		    "} else {".
				"document.cookie=c;".
				"if(k){document.cookie=k;}";
		if ($reload) {
			$output .= "document.location.reload();";
		}
		$output .= "}";
		$output .= "}catch(e){}"."";
		return $output;
	}

	/**
	* Reads in the cookie values and breaks them up into an object for use in build()
	* @param  {String}       the value from the cookie
	*
	* from modernizr-server
	*
	* @return {Object}       key/value pairs based on the cookie
	*/
	private static function _ang($cookie) {
		$uaFeatures = new Detector();
		if ($cookie != '') {
			foreach (explode('|', $cookie) as $feature) {
				list($name, $value) = explode(':', $feature, 2);
				if ($value[0]=='/') {
					$value_object = new stdClass();
					foreach (explode('/', substr($value, 1)) as $sub_feature) {
						list($sub_name, $sub_value) = explode(':', $sub_feature, 2);
						$value_object->$sub_name = $sub_value;
					}
					$uaFeatures->$name = $value_object;
				} else {
					$uaFeatures->$name = $value;
				}
			}
		}
		return $uaFeatures;
	}

	/**
	* Builds a noscript link so the page will reload
	*
	* @return {String}       string that is the URL for the noscriptlink
	*/
	private static function buildNoscriptLink() {
		// build the noscript link just in case
		$noscriptLink = $_SERVER["REQUEST_URI"];
		if (isset($_SERVER["QUERY_STRING"]) && ($_SERVER["QUERY_STRING"] != "")) {
			$noscriptLink .= "?".$_SERVER["QUERY_STRING"]."&nojs=true";
		} else {
			$noscriptLink .= "?nojs=true";
		}
		return $noscriptLink;
	}
	
	/**
	* Builds a link to the features.js.php file that addresses if cookies are supported or not
	*
	* @return {String}       string that is the URL for the noscriptlink
	*/
	public static function buildFeaturesScriptLink() {
		$nocookies = (isset($_REQUEST["nocookies"]) && ($_REQUEST["nocookies"] == "true")) ? "&nocookies=true" : ""; 
		print "<script type=\"text/javascript\" src=\"".self::$featuresScriptWebPath."features.js.php?dynamic=true".$nocookies."\"></script>";
	}
	
	/**
	* Adds the user agent hash and user agent to a list for retrieval in the demo (or for any reason i guess)
	* Important: This is a performance hit so enable with caution. I only had this for detector.dmolsen.com
	*/
	private static function addToUAList() {
		// open user agent list and decode the JSON
		if ($uaListJSON = @file_get_contents(__DIR__."/".self::$uaDirCore."ua.list.json")) {
			$uaList = json_decode($uaListJSON);
		} 
		
		// merge the old list with the new user agent
		$mergedInfo = (object) array_merge((array) $uaList, array(self::$uaHash => self::$ua));
		
		// write out the data to the user agent list
		$uaListJSON = json_encode($mergedInfo);
		$fp = fopen(__DIR__."/".self::$uaDirCore."ua.list.json", "w");
		fwrite($fp, $uaListJSON);
		fclose($fp);
		
	}
	
	/**
	* Returns the first twp characters of the uaHash so Detector can build out directories
	* @param  {String}        uaHash to be substringed
	*
	* @return {String}        the first five characters of the hash
	*/
	private static function uaDir($uaHash = false) {
		$uaHash = $uaHash ? $uaHash : self::$uaHash; 
		return substr($uaHash,0,2)."/";
	}
	
	/**
	* Writes out the UA file to the specified location
	* @param  {String}        encoded JSON
	* @param  {String}        file path
	*/
	private static function writeUAFile($jsonEncoded,$uaFilePath) {
		$dir = self::uaDir();
		if (!is_dir(__DIR__."/".self::$uaDirCore.$dir)) {
			// create the files and then change permissions
			mkdir(__DIR__."/".self::$uaDirCore.$dir);
			chmod(__DIR__."/".self::$uaDirCore.$dir,0775);
			mkdir(__DIR__."/".self::$uaDirExtended.$dir);
			chmod(__DIR__."/".self::$uaDirCore.$dir,0775);
		}
		$fp = fopen($uaFilePath, "w");
		fwrite($fp, $jsonEncoded);
		fclose($fp);
		chmod($uaFilePath,0664);
	}
	
	/**
	* Opens the UA file at the specificed location
	* @param  {String}        file path
	*
	* @return {Object}        object containing the results of opening a file & parsing the JSON in the UA file
	*/
	private static function openUAFile($uaFilePath) {
		// open the JSON template extended file that will be populated & start populating its object
		if ($uaJSONTemplate = @file_get_contents($uaFilePath)) {
			$uaJSONTemplate = json_decode($uaJSONTemplate);
			return $uaJSONTemplate;
		} else {
			print "couldn't open the JSON file at ".$uaFilePath." for some reason. permissions? bad path? bombing now...";
			exit;
		}
	}
	
	/**
	* reads out all the files in a directory
	* @param  {String}        file path
	* @param  {Boolean}       should a boolean value that shows if files are in the directory be returned
	*
	* @return {Boolean}       assuming it's requested the boolean value of if the dir has files will be returned
	*/
	private static function readDirFiles($dir, $returnBool = false) {
		$dirHasFiles = false;
		$entries = scandir(__DIR__ .'/'. $dir);
		foreach($entries as $entry) {
			if (($entry != ".") && ($entry != "..") && ($entry != "README") && (strpos($entry,"_") !== 0)) {
	            if (!$returnBool) {
					readfile(__DIR__ .'/'. $dir . $entry);
				}
				$dirHasFiles = true;
	        }
		}
		if ($returnBool) {
			return $dirHasFiles;
		}
	}
	
	/**
	* Parses the cookie for a list of features
	* @param  {String}        file path
	* @param  {Object}        the object to be modified/added too
	* @param  {Boolean}       if this is the main cookie ingest of info
	*
	* @return {Object}        values from the cookie for that cookieExtension
	*/
	private static function parseCookie($cookieExtension,$obj,$default = false) {
		$cookieName = $default ? self::$cookieID : self::$cookieID."-".$cookieExtension;
		if (isset($_COOKIE[$cookieName])) {
			$uaFeatures = self::_ang($_COOKIE[$cookieName]);
			foreach($uaFeatures as $key => $value) {
				if ((strpos($key,$cookieExtension."-") !== false) || (($cookieExtension == 'core') && (strpos($key,"extended-") === false) && (strpos($key,"pr-") === false) && (strpos($key,"ps-") === false))) {
					$key = str_replace($cookieExtension."-", "", $key);
					if (is_object($value)) {
						foreach ($value as $vkey => $vvalue) {
							if ($vvalue == "probably") { // hack for modernizr
								$value->$vkey = true;
							} else if ($vvalue == "maybe") { // hack for modernizr
								$value->$vkey = false;
							} else if (($vvalue == 1) || ($vvalue == 0)) {
								$value->$vkey = ($vvalue == 1) ? true : false;
							} else {
								$value->$vkey = $vvalue;
							}
						}
						$obj->$key = $value;
					} else {
						$obj->$key = ($value == 1) ? true : false;
					}
				}
			}
			return $obj;
		}
	}
	
	/**
	* Adds the user agent hash and user agent to a list for retrieval in the demo (or for any reason i guess)
	*
	* @return {Boolean}       the result of checking the current user agent string against a list of bots
	*/
	private static function checkSpider() {
		$botRegex = '(bot|borg|google(^tv)|yahoo|slurp|msnbot|msrbot|openbot|archiver|netresearch|lycos|scooter|altavista|teoma|gigabot|baiduspider|blitzbot|oegp|charlotte|furlbot|http%20client|polybot|htdig|ichiro|mogimogi|larbin|pompos|scrubby|searchsight|seekbot|semanticdiscovery|silk|snappy|speedy|spider|voila|vortex|voyager|zao|zeal|fast\-webcrawler|converacrawler|dataparksearch|findlinks)';
		return preg_match("/".$botRegex."/i",self::$ua);
	}
	
	/**
	* Adds the user agent hash and user agent to a list for retrieval in the demo (or for any reason i guess)
	* @param  {Object}        the core template object
	*
	* @return {Object}        the core template object "filled out" from ua-parser-php
	*/
	private static function createUAProperties($obj) {
		
		// include the ua-parser-php library to rip apart user agent strings
		require_once(__DIR__."/lib/ua-parser-php/UAParser.php");
		
		// classify the user agent string so we can learn more what device this really is. more for readability than anything
		$userAgent = UA::parse();
		
		// save properties from ua-parser-php
		foreach ($userAgent as $key => $value) {
			$obj->$key = $value;
		}
		
		return $obj;
	}
	
}

// if this is a request from features.js.php don't run the build function
if (!isset($p)) {
	$ua = Detector::build();
	
	// include the browserFamily library to classify the browser by features
	require_once(__DIR__."/lib/feature-family/featureFamily.php");
	$ua->family = featureFamily::find($ua);
}

?>