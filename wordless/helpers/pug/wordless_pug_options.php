<?php

class WordlessPugOptions {
    public static function get_options() {
        $wp_debug = defined('WP_DEBUG') ? WP_DEBUG : false;
        return array(
            'pretty' => true,
            'expressionLanguage' => 'php',
            'extension' => '.pug',
            'cache' => Wordless::theme_temp_path(),
            'strict' => true,
            'debug' => $wp_debug,
            'enable_profiler' => false,
            'error_reporting' => E_ERROR | E_USER_ERROR
        );
    }
}
