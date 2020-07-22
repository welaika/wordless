<?php
function create_acf_block()
 { $helper = Wordless::helper('AcfGutenbergBlockHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'create_acf_block'), $args); }

