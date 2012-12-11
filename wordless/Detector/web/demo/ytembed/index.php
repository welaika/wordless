<?php 

// require Detector so we can popular identify the browser & populate $ua
require("../../../lib/Detector/Detector.php"); 

$html5Embed = "<iframe src=\"http://www.youtube.com/embed/N-zXaiDNKjU\" frameborder=\"0\" allowfullscreen></iframe>";
$simpleLink   = "Your browser doesn't appear to support HTML5. <a href=\"http://www.youtube.com/watch?v=N-zXaiDNKjU\">Check out the video on YouTube</a>.";

// switch templates based on device type
if ($ua->isMobile) {
	include("templates/index.mobile.inc.php");
} else {
	include("templates/index.default.inc.php");
}

?>
