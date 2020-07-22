<?php
function simple_fields_meta()
 { $helper = Wordless::helper('SimpleFieldsHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'simple_fields_meta'), $args); }

