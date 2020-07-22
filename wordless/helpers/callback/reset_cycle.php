<?php
function reset_cycle()
 { $helper = Wordless::helper('TextHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'reset_cycle'), $args); }

