Modernizr.addTest("core-mobile", function() {
	if (Modernizr.mq('only screen and (max-width: 320px) and (orientation: portrait)')) {
		return true;
	} else if (Modernizr.mq('only screen and (max-width: 480px) and (orientation: landscape)')) {
		return true;
	} else {
		return false;
	}
});