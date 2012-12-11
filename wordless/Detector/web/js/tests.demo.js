/*
 * Including the extra core, extended, and per request tests from base Detector for use in the demo.
 */

// JSON support
Modernizr.addTest('json', !!window.JSON && !!JSON.parse);

// Overflowscrolling (iOS) support
Modernizr.addTest("overflowscrolling",function(){ return Modernizr.testAllProps("overflowScrolling"); });

// Emoji support
// Requires a Modernizr build with `canvastext` included
// http://www.modernizr.com/download/#-canvas-canvastext
Modernizr.addTest('extended-emoji', function() {
  if (!Modernizr.canvastext) return false;
  var node = document.createElement('canvas'),
      ctx = node.getContext('2d');
  ctx.textBaseline = 'top';
  ctx.font = '32px Arial';
  ctx.fillText('\ud83d\ude03', 0, 0); // "smiling face with open mouth" emoji
  return ctx.getImageData(16, 16, 1, 1).data[0] != 0;
});

// Device Pixel Ratio
Modernizr.addTest('ps-hiResCapable', Modernizr.mq('only screen and (-webkit-min-device-pixel-ratio: 1.5), only screen and (-o-min-device-pixel-ratio: 3/2), only screen and (min-device-pixel-ratio: 1.5)'));

// Select screen attributes
Modernizr.addTest("pr-screenAttributes",function() { 
	var _windowHeight = (window.innerHeight > 0) ? window.innerHeight : screen.width;
	var _windowWidth  = (window.innerWidth > 0) ? window.innerWidth : screen.width;
	var _colorDepth   = screen.colorDepth;
	
	return { windowHeight: _windowHeight, windowWidth: _windowWidth, colorDepth: _colorDepth };
});

Modernizr.addTest("core-mobile", function() {
	if (Modernizr.mq('only screen and (max-width: 320px) and (orientation: portrait)')) {
		return true;
	} else if (Modernizr.mq('only screen and (max-width: 480px) and (orientation: landscape)')) {
		return true;
	} else {
		return false;
	}
});

Modernizr.addTest("core-tablet",function() {
	if (Modernizr.mq('only screen and (min-width: 600px) and (orientation:portrait)')) {
		return true;
	} else if (Modernizr.mq('only screen and (max-width: 1024px) and (orientation:landscape)')) {
		return true;
	} else {
		return false;
	}
});

Modernizr.addTest("core-desktop",Modernizr.mq('only screen and (min-width: 802px)'));