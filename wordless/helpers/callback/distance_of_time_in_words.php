<?php
function distance_of_time_in_words()
 { $helper = Wordless::helper('DateHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'distance_of_time_in_words'), $args); }

