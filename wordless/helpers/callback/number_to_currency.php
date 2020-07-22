<?php
function number_to_currency()
 { $helper = Wordless::helper('NumberHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'number_to_currency'), $args); }

