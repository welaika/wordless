<?php
function placeholder_text()
 { $helper = Wordless::helper('FakerHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'placeholder_text'), $args); }

