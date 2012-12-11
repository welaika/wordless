<?php 
    // to be included in your <head></head> tag to run the per-request tests
	$p = true; // turn off the build function
	require("path/to/Detector/Detector.php");
	header("content-type: application/x-javascript");
	Detector::perrequest();
?>