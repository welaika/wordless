<?php 

// require Detector so we can popular identify the browser & populate $ua
require("../lib/Detector/Detector.php"); 

// include some helpful functions
include("templates/_convertTF.inc.php");
include("templates/_createFT.inc.php");

// switch templates based on device type
if (isset($ua->isMobile) && $ua->isMobile && (Detector::$foundIn != "archive")) {
	include("templates/index.mobile.inc.php");
} else {
	include("templates/index.default.inc.php");
}

?>

