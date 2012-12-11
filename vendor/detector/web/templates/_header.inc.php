<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Detector [BETA] - combined browser- &amp; feature-detection for your app</title>
    <meta name="description" content="">
    <meta name="author" content="">
	<?php 
		if (isset($ua->isMobile) && $ua->isMobile && (Detector::$foundIn != "archive")) {
			print "<meta name=\"viewport\" content=\"width=device-width\">";
		}
	?>

    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le styles -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
	<?php 
		if (isset($ua->isMobile) && $ua->isMobile && (Detector::$foundIn != "archive")) {
			print "<link href=\"/css/mobile.css\" rel=\"stylesheet\">";
		} else {
			print "<link href=\"/css/desktop.css\" rel=\"stylesheet\">";
		}
	?>
	<style type="text/css">
		.beta {
			font-size: 20px;
			vertical-align: bottom;
		}
		
		thead > tr > th { 
			vertical-align: bottom; 
		}
		
		thead > tr, div.alert-message {
			background-color: #efefef;
			background-image: none;
		}
		
		.featureNote {
			border: 0;
			padding: 0;
			margin: -5px 0 25px 4px;
			color: #666;
			font-size: 13px;
		}
	</style>

	<!-- My Scripts -->
	<?php Detector::buildFeaturesScriptLink(); ?>
	<script type="text/javascript" src="/js/modernizr.2.5.2.min.custom.js"></script>
	<script type="text/javascript" src="/js/tests.demo.js"></script>
  </head>

  <body>

    <div class="container">

      <div class="content">
        <div class="page-header">
          <h1><a href="/" style="color: black;">Detector</a> <span class='label notice beta' >beta</span> <?php if (isset($ua->isMobile) && $ua->isMobile && (Detector::$foundIn != "archive")) {} else { print "<small>combined browser- &amp; feature-detection for your app</small></h1>"; } ?>
        </div>
        <div class="row">