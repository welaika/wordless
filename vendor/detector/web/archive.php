<?php

// require Detector so we can popular identify the browser & populate $ua
require("../lib/Detector/Detector.php"); 

if ($ua->isMobile) {
	include("templates/archive.mobile.inc.php");
} else {
	include("templates/archive.default.inc.php");
}

?>
