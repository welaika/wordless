<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Modernizr Listing</title>
	<script type="text/javascript" src="/js/modernizr.2.5.2.min.custom.js"></script>
  </head>

  <style type="text/css">
	body { 
		font-family: sans-serif;
		font-size: 12px;
	}
  </style>
  <body>

	<h1>Listing of Modernizr Properties</h1>
	<p>The following properties are being tracked by default with Detector using Modernizr 2.5.2.</p>
	<p>
	<script type="text/javascript">	
	 var m=Modernizr;
	 for(var f in m){
	    if(f[0]=='_'){continue;}
	    var t=typeof m[f];
	    if(t=='function'){continue;}
	    	document.write(f);
	    	if(t=='object'){
				document.write('-><br />');
	      		for(var s in m[f]){
					if (typeof m[f][s]=='boolean') { document.write('&nbsp; &nbsp; &nbsp; '+s+'<br />'); }
	        		else { document.write('&nbsp; &nbsp; &nbsp; '+s+'<br />'); }
	      		}
	    	} else {
				document.write('<br />');
			}
	  	}
	</script>
	</p>
  </body>
</html>