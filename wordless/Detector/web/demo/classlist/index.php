<?php

// require detector to get the family, autoloads the $ua var
require_once "../../../lib/Detector/Detector.php";

?>

<html class="<?=Detector::createHTMLList($ua,"isMobile,geolocation,cssanimations,cssgradients,indexeddb",true)?>">
	<head>
		<title>Demo of Including Detector Features in the HTML Tag</title>
	</head>
	<body>
		View the source and you'll see the HTML tag is modified with the following attributes select attributes:<br />
		<br />
		<!-- by using true as the last object you're saying you want select UA attributes also shared -->
		<?=Detector::createHTMLList($ua,"isMobile,geolocation,cssanimations,cssgradients,indexeddb",true)?>
	</body>
</html>