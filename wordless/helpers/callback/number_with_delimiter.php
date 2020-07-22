<?php
function number_with_delimiter()
 { $helper = Wordless::helper('NumberHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'number_with_delimiter'), $args); }

