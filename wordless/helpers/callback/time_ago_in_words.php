<?php
function time_ago_in_words()
 { $helper = Wordless::helper('DateHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'time_ago_in_words'), $args); }

