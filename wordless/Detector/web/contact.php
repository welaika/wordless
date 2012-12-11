<?php

// require Detector so we can popular identify the browser & populate $ua
require("../lib/Detector/Detector.php"); 

if ($_POST['post']) {
	$message = "Here is some feedback for Detector:

Email addy: 
".$_POST['email']."

Their message:
".$_POST['message'];

	mail('dmolsen@gmail.com', 'Detector Feedback', $message);
}

if ($ua->isMobile) {
	include("templates/contact.mobile.inc.php");
} else {
	include("templates/contact.default.inc.php");
}

?>
