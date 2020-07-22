<?php
function time_tag()
 { $helper = Wordless::helper('DateHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'time_tag'), $args); }

