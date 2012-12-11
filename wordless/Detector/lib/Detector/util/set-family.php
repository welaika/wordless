<?php

/*!
 * Detector Util - Update family property across all profiles
 *
 * Copyright (c) 2011-2012 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 */

// include the browserFamily lib to figure out what family this belongs too
require(__DIR__."/../lib/feature-family/featureFamily.php");

if ($uaListJSON = @file_get_contents(__DIR__."/../user-agents/core/ua.list.json")) {
	$uaList = (array) json_decode($uaListJSON);
	asort($uaList);
	foreach ($uaList as $key => $value) {
		$fileCore             = @file_get_contents(__DIR__."/../user-agents/core/ua.".$key.".json");	
		$fileExtended         = @file_get_contents(__DIR__."/../user-agents/extended/ua.".$key.".json");
		$jsonCore             = json_decode($fileCore);
		$jsonExtended         = json_decode($fileExtended);
		$jsonMerged           = (object) array_merge((array) $jsonCore, (array) $jsonExtended);	
		$jsonExtended->family = featureFamily::find($jsonMerged);
		$jsonUpated           = json_encode($jsonExtended);
		$fp = fopen(__DIR__."/../user-agents/extended/ua.".$key.".json", "w");
		fwrite($fp, $jsonUpated);
		fclose($fp);
	}
}

?>