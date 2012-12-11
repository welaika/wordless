<?php

/*!
 * Detector Util - Convert old UA info to new UA info
 *
 * Copyright (c) 2011-2012 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 */

// include the ua-parser-php library to rip apart user agent strings
require(__DIR__."/../lib/ua-parser-php/UAParser.php");

if ($uaListJSON = @file_get_contents(__DIR__."/../user-agents/core/ua.list.json")) {
	$uaList = (array) json_decode($uaListJSON);
	asort($uaList);
	foreach ($uaList as $key => $value) {
		$file = __DIR__."/../user-agents/core/ua.".$key.".json";	
		if (!isset($ua->device)) {
			$uaItemJSON  = @file_get_contents($file);
			$uaJSON      = json_decode($uaItemJSON);
			unset($uaJSON->deviceOSGeneral);
			unset($uaJSON->deviceOSSpecific);
			unset($uaJSON->majorVersion);
			unset($uaJSON->minorVersion);
			unset($uaJSON->isTablet);
			unset($uaJSON->isMobile);
			unset($uaJSON->isComputer);
			unset($uaJSON->isSpider);
			unset($uaJSON->iOSUIWebview);
			$userAgent   = UA::parse($uaJSON->ua);
			$updatedInfo = (object) array_merge((array) $uaJSON, (array) $userAgent);
			$updatedInfo = json_encode($updatedInfo);
			$fp = fopen($file, "w");
			fwrite($fp, $updatedInfo);
			fclose($fp);
		}
	}
}

?>