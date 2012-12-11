# Detector v0.8.5 #

Detector is a simple, PHP- and JavaScript-based browser- and feature-detection library that can adapt to new devices & browsers on its own without the need to pull from a central database of browser information.
	
Detector dynamically creates profiles using a browser's _(mainly)_ unique user-agent string as a key. Using [Modernizr](http://modernizr.com/) it records the HTML5 & CSS3 features a requesting browser may or may not support. [ua-parser-php](https://github.com/tobie/ua-parser/tree/master/php) is used to collect and record any useful information _(like OS or device name)_ the user-agent string may contain. 

With Detector a developer can serve the appropriate markup, stylesheets, and JavaScript to a requesting browser without being completely dependent on a front-end-only resource loader nor a browser-detection library being up-to-date.

The server-side portion of Detector is based upon [modernizr-server](https://github.com/jamesgpearce/modernizr-server) by James Pearce ([@jamespearce](http://twitter.com/#!/jamespearce)) and the browser-detection library [ua-parser-php](https://github.com/tobie/ua-parser/tree/master/php). Detector utilizes [Modernizr](http://www.modernizr.com/) for its client-side, feature-detection support.

## Demo of Detector ##

A very [simple demo of Detector](http://detector.dmolsen.com/) is available for testing. It's also now in production on the [West Virginia University home page](http://www.wvu.edu/).

## Features ##

The following features are as of v0.8.5 of Detector:

* stores features detected with [Modernizr 2.5.2](http://www.modernizr.com/) ([list](http://detector.dmolsen.com/demo/modernizr-listing/)) and browser & device information detected with [ua-parser-php](https://github.com/tobie/ua-parser/tree/master/php) (based on [ua-parser](http://code.google.com/p/ua-parser/)) on the server as part of a browser profile for easy retrieval
* uses the user agent string as a unique key for looking up information (e.g. one profile per user agent)
* majority of tests are run only once per unique user agent string so only one user is ever tested & redirected
* [add your own feature tests](https://github.com/dmolsen/Detector/wiki/Detector-Test-Tutorial) and store the results using Modernizr's addTest() API
* tests can be created that run once per user agent, once per session, or on every request
* version your browser profiles so you can force them to be recreated after adding new tests
* [easily organize browsers into families](https://github.com/dmolsen/Detector/wiki/Detector-Family-Tutorial) based on a mix of supported features & device information
* browser & bots that don't use JavaScript or cookies can still use your site using a default browser profile
* provide default families for search engines and browsers that don't support javascript or cookies so your best side is always showing
* give your mobile users a "desktop" view via family switching (assuming you use a templating system)
* push feature information to the browser via CSS classes and/or a JavaScript object a la Modernizr
* use with a templating system like Mustache to [create a RESS system](https://github.com/dmolsen/Detector/wiki/Templating-with-Detector-&-Mustache-Tutorial)

## Tutorials ##

* [Adding & Using Detector With Your Application](https://github.com/dmolsen/Detector/wiki/Adding-&-Using-Detector-With-Your-Application)
* [Creating Browser Groupings](https://github.com/dmolsen/Detector/wiki/Detector-Family-Tutorial)
* [Creating Feature Tests for Detector](https://github.com/dmolsen/Detector/wiki/Detector-Test-Tutorial)
* [Pushing Detector Data to the Browser](https://github.com/dmolsen/Detector/wiki/Pushing-Detector-Data-to-the-Browser-Tutorial)
* [Templating with Detector & Mustache](https://github.com/dmolsen/Detector/wiki/Templating-with-Detector-&-Mustache-Tutorial)

## More Information ##

* [How Detector Works](https://github.com/dmolsen/Detector/wiki/How-Detector-Works)
* [RESS, Server-Side Feature-Detection and the Evolution of Responsive Web Design](http://www.dmolsen.com/mobile-in-higher-ed/2012/02/21/ress-and-the-evolution-of-responsive-web-design/)
* [Why I Created Detector](http://www.dmolsen.com/mobile-in-higher-ed/2012/01/18/introducing-detector-combining-browser-feature-detection-for-your-web-app/)

## Credits ##

First and foremost, thanks to James Pearce ([@jamespearce](http://twitter.com/jamespearce)) for putting together [modernizr-server](https://github.com/jamesgpearce/modernizr-server) and giving me a great base to work from. I also took some of the copy from his README and used it in the section, "Adding Detector to Your Application."  Also, thanks to the guys behind [Modernizr](http://www.modernizr.com/) for giving developers a great lib as well as the the ability to expand Modernizr via `Modernizr.addTest()`. Finally, thanks to Bryan Rieger ([@bryanrieger](http://twitter.com/bryanrieger)) & Stephanie Rieger ([@stephanierieger](http://twitter.com/stephanierieger)) of Yiibu and Luke Wroblewski ([@lukew](http://twitter.com/lukew)) for providing inspiration via [Profile](https://github.com/yiibu/profile) and [RESS](http://www.lukew.com/ff/entry.asp?1392) respectively.