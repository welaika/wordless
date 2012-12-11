<?php 
	$p = true; // turn off the build function
	require("../../lib/Detector/Detector.php");
	header("content-type: application/x-javascript");
	Detector::perrequest();
?>