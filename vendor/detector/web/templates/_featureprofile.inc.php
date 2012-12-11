<h3><?= (Detector::$foundIn == 'archive') ? 'Archived' : 'Your'; ?> Detector Feature Profile</h3>
<p>
	The following feature profile was primarily created using <a href="http://www.modernizr.com/docs/#s2">Modernizr's core tests</a>. The left column of results, <strong>Your Browser</strong>, is populated by JavaScript using a copy of Modernizr that is loaded with this page. The right column, <strong>Detector Profile</strong>, is populated by PHP using the profile created by Detector for your browser.
	In addition to the core tests
	I've added an extended test that checks for emoji support as well as a per request test to check the device pixel ratio. Both were added using the <a href="http://www.modernizr.com/docs/#addtest">Modernizr.addTest() Plugin API</a>.
	To learn more about core, extended, and per request tests please <a href="https://github.com/dmolsen/Detector">review the README</a>.  To access any of these options in your PHP app you'd simply type <code>$ua->featureName</code>.
	<br /><br />
</p>

<table class="zebra-striped span9">
	<thead>
		<tr>
			<th colspan="2">Feature Profile Properties</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th class="span3">coreVersion:</th>
			<td>
				<? 
					if (isset($ua->coreVersion)) {
						print $ua->coreVersion;
				   	} else {
						print "This profile hasn't been versioned yet.";
					}
				?>
			</td>
		</tr>
		<tr>
			<th class="span3">family:</th>
			<td>
				<? 
					if (isset($ua->family)) {
						print $ua->family;
				   	} else {
						print "Feature family hasn't been set yet for this profile.";
					}
				?>
			</td>
		</tr>

	</tbody>
</table>
<div class="featureNote span9">
	<small><em>To learn more about families please <a href="https://github.com/dmolsen/Detector/wiki/Detector-Family-Tutorial">review the family tutorial</a>.</em></small>
</div>

<?php 
	
	$ua_a = (array) $ua;
	ksort($ua_a);
	$ua = (object) ($ua_a);
	
	// organize what features show up in which section
	$css3Features       = "/(fontface|backgroundsize|borderimage|borderradius|boxshadow|flexbox|flexbox-legacy|hsla|multiplebgs|opacity|rgba|textshadow|cssanimations|csscolumns|generatedcontent|cssgradients|cssreflections|csstransforms|csstransforms3d|csstransitions|overflowscrolling|bgrepeatround|bgrepeatspace|bgsizecover|boxsizing|cubicbezierrange|cssremunit|cssresize|cssscrollbar)/";
	$html5Features      = "/(adownload|applicationcache|canvas|canvastext|draganddrop|hashchange|history|audio|video|indexeddb|input|inputtypes|localstorage|postmessage|sessionstorage|websockets|websqldatabase|webworkers|contenteditable|webaudio|audiodata|userselect|dataview|microdata|progressbar|meter|createelement-attrs|time|geolocation|devicemotion|deviceorientation|speechinput|filereader|filesystem|fullscreen|formvalidation|notification|performance|quotamanagement|scriptasync|scriptdefer|webintents|websocketsbinary|blobworkers|dataworkers|sharedworkers)/";
	$miscFeatures       = "/(touch|webgl|json|lowbattery|cookies|battery|gamepad|lowbandwidth|eventsource|ie8compat|unicode)/";
	$mqFeatures         = "/(mediaqueries|desktop|mobile|tablet)/";
	$extendedFeatures   = "/(extendedVersion|emoji)/";
	$perSessionFeatures    = "/(hirescapable)/";
	$perRequestFeatures = "/(screenattributes)/";
	
	// create separate tables
	createFT($ua,$css3Features,"CSS3 Features");
	createFT($ua,$html5Features,"HTML5 Features");
	createFT($ua,$miscFeatures,"Misc. Features","","While a device may be touch-based that doesn't not mean it supports <a href=\"http://www.w3.org/TR/touch-events/\">touch events</a> which is what I'm testing for here.");
	createFT($ua,$mqFeatures,"Browser Class via Media Queries","core-","This feature needs some love as it's not always returning information correctly.");
	createFT($ua,$extendedFeatures,"Detector Extended Test Features","extended-","To learn more about extended tests and their purpose please <a href=\"https://github.com/dmolsen/Detector/wiki/Detector-Test-Tutorial\">review the test tutorial.</a>");
	if (Detector::$foundIn != 'archive') {
		createFT($ua,$perSessionFeatures,"Detector Per Session Test Features","ps-","To learn more about per session tests and their purpose please <a href=\"https://github.com/dmolsen/Detector/wiki/Detector-Test-Tutorial\">review the test tutorial.</a>");
		createFT($ua,$perRequestFeatures,"Detector Per Request Test Features","pr-","To learn more about per request tests and their purpose please <a href=\"https://github.com/dmolsen/Detector/wiki/Detector-Test-Tutorial\">review the test tutorial.</a> If this section isn't populated hit \"refresh\". Attributes are captured via a cookie. Screen size will also be one request behind if you resize the window for the same reason.");	
	}
?>