<?php
function cycle()
 { $helper = Wordless::helper('TextHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'cycle'), $args); }

