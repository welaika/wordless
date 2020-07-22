<?php
function get_the_time_ago()
 { $helper = Wordless::helper('DateHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'get_the_time_ago'), $args); }

