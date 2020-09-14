<?php

class WordlessWpConfigTesting {
    public function __construct() {

    }

    public static function current_theme() : string {
        return get_option('current_theme');
    }

    public static function template_path() : string {
        return Wordless::join_paths(__DIR__ . '/wp-config-test-template.php');
    }

    public static function destination_path() : string {
        return Wordless::join_paths(ABSPATH, 'wp-config.php');
    }

    public static function install() : void {
        $source_content = file_get_contents(self::template_path());
        $source_content = str_replace("%THEME_NAME%", self::current_theme(), $source_content);
        file_put_contents(self::destination_path(), $source_content);
        chmod(self::destination_path(), intval(0664, 8));
    }
}
