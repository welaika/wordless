<?php
/**
 * @file
 * Requires all defined helpers file, both from HamlParser ( phamlp ) and from
 * Wordless.
 */
require_once Wordless::join_paths(dirname(dirname(__FILE__)), 'vendor/phamlp/haml/HamlParser.php');

Wordless::require_once_dir(Wordless::join_paths(dirname(__FILE__), "helpers"));

