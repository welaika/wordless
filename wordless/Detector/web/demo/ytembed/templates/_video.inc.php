<h2>Server-side Feature Detection</h2>
<p>
	In Anders Andersen's (<a href="http://twitter.com/andmag">@andmag</a>) recent post, <em><a href="http://amobil.se/2012/01/responsive-embeds-ress/">Responsive embeds + RESS</a></em>,
	he closes wondering if feature detection could be used to "responsively" include the video element in a web page. Taking up that challenge I was curious to see if <a href="http://detector.dmolsen.com/">Detector</a> could
	handle this example. If Detector supported a Flash test I'd feel a little better about the results but it should work out ok.
</p>
<h3>HTML5 Video Test</h3>
<p>
	The following example is using Detector's HTML5 video profile for the requesting browser. So if your browser supports the <code>&lt;video&gt;</code> tag and <code>WebM</code> or <code>H.264</code> a video should be playable below. Detector doesn't currently support a check for a Flash player so for other devices
	you'll simply get an ugly link to the video on YouTube. If Detector did support that check then I could fall back to the old embed code first and then fall back to the ugly link.
</p>
<div class="embed-container">
<?php 
	if ($ua->video->h264 || $ua->video->webm) { 
		print $html5Embed;
	} else {
		print $simpleLink;
	}
?>
</div>
<h3>The Code</h3>
<p>
	After including the Detector lib at the top of the script this is the very simple switch that can be included in your code:
</p>
<p style="padding-left: 20px;">
	<code>
	if ($ua->video->h264 || $ua->video->webm) {<br />
	&nbsp; &nbsp; &nbsp; print $html5Embed; // YouTube's &lt;iframe&gt; code<br />
	} else {<br />
	&nbsp; &nbsp; &nbsp; print $simpleLink;<br />
	}
	</code>
</p>
<p>
	Easy peasy.
</p>
	
