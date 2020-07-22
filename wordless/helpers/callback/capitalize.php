<?php
function capitalize()
 { $helper = Wordless::helper('TextHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'capitalize'), $args); }

