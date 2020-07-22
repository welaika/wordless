<?php
function simple_fields_metas()
 { $helper = Wordless::helper('SimpleFieldsHelper');
 $args = func_get_args();
 return call_user_func_array(array($helper, 'simple_fields_metas'), $args); }

