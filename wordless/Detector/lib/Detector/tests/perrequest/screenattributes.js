// should be modified to return an object... with different possible color depths set to true/false?
Modernizr.addTest("pr-screenAttributes",function() { 
	var screenAttributes = {};
	screenAttributes.windowHeight = (window.innerHeight > 0) ? window.innerHeight : screen.width;
	screenAttributes.windowWidth  = (window.innerWidth > 0) ? window.innerWidth : screen.width;
	screenAttributes.colorDepth   = screen.colorDepth;
	return screenAttributes;
});