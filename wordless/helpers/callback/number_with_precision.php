<?php
function number_with_precision()
 { $helper = Wordless::helper('NumberHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'number_with_precision'), $args); }

