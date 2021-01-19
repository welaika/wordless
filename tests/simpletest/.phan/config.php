<?php
/** 
 * Phan Configuration
 *
 * This configuration will overlaid on top of the default configuration. 
 * Command line arguments will be applied after this file is read.
 * See Config for all configurable options.
 *
 * @link https://github.com/etsy/phan
 * @see  src/Phan/Config.php  
 */
return [   
    // whitelist 
    'directory_list'                                => [ '.', 'test', 'extensions'],
    // blacklist
    'exclude_analysis_directory_list'               => [ './vendor', '.\vendor', 'vendor/', 'doc/', 'packages/', 'tutorials/'],
    // suppression
    'suppress_issue_types'                          => [ 'PhanUndeclaredClassMethod'],
    // checks
    'allow_missing_properties'                      => false,
    'analyze_signature_compatibility'               => true,
    'backward_compatibility_checks'                 => true,
    'dead_code_detection'                           => true,
    'ignore_undeclared_variables_in_global_scope'   => false,
    'minimum_severity'                              => 10,
    'null_casts_as_any_type'                        => true,
    'quick_mode'                                    => false,
    'scalar_implicit_cast'                          => true,
    'should_visit_all_nodes'                        => true,
];