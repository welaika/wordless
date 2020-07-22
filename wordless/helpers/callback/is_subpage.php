<?php
function is_subpage()
 { $helper = Wordless::helper('ConditionalHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'is_subpage'), $args); }

