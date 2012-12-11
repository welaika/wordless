<?php

/* Simply prints out the name of the family based on the user agent using 
   families.json without actually updating any of the profiles.
   You can also supply ?pid=[something] if you want */

// require Detector so we can popular identify the browser & populate $ua
require("../../../lib/Detector/Detector.php"); 

print "family name: ".featureFamily::find($ua);

?>