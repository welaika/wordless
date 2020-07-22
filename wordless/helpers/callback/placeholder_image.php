<?php
function placeholder_image()
 { $helper = Wordless::helper('FakerHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'placeholder_image'), $args); }

