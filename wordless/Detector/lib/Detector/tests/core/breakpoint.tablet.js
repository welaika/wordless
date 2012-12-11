Modernizr.addTest("core-tablet",function() {
	if (Modernizr.mq('only screen and (min-width: 600px) and (orientation:portrait)')) {
		return true;
	} else if (Modernizr.mq('only screen and (max-width: 1024px) and (orientation:landscape)')) {
		return true;
	} else {
		return false;
	}
});