function getCookie() {
	var i,x,y,ARRcookies=document.cookie.split(";");
	for (i=0;i<ARRcookies.length;i++) {
  		x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
	  	y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
	  	x=x.replace(/^\s+|\s+$/g,"");
	  	if (x == "testCookie") {
	    	return y;
	    }
	}
}

document.cookie = "testCookie=testData";
var cookieRedirect = (window.location.href.match(/\?/)) ? window.location.href + "&nocookies=true" : window.location.href + "?nocookies=true";
