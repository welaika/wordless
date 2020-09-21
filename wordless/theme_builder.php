<?php

/**
* WordlessThemeBuilder
**/
class WordlessThemeBuilder {

    function __construct($theme_name, $theme_dir, $chmod_set) {
        $this->source_path = Wordless::join_paths(dirname(__FILE__), "theme_builder", "vanilla_theme");
        $this->theme_dir = Wordless::join_paths(dirname(get_template_directory()), $theme_dir);
        $this->current_theme_dir = get_template_directory();
        $this->theme_name = $theme_name;
        $this->chmod_set = $chmod_set;
    }

    public function build() {
        $this->recursive_copy($this->source_path, $this->theme_dir);
    }

    public function upgrade_theme_config() : bool {
        foreach (Wordless::$webpack_files_names as $key => $filename) {
            $copied = copy(
                Wordless::join_paths($this->source_path, $filename),
                Wordless::join_paths($this->current_theme_dir, $filename)
            );

            if ( ! $copied ) { return false; }
        }

        return true;
    }

    private function recursive_copy($src, $dst) {
        $dir = opendir($src);
        $this->make_directory($dst);
        while(false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recursive_copy($src . '/' . $file,$dst . '/' . $file);
                } else {
                    $source_content = file_get_contents($src . '/' . $file);
                    $source_content = str_replace("%THEME_NAME%", $this->theme_name, $source_content);
                    $source_content = str_replace("%ABSPATH%", ABSPATH, $source_content);
                    file_put_contents($dst . '/' . $file, $source_content);
                    chmod($dst . '/' . $file, $this->chmod_set);
                }
            }
        }
        closedir($dir);
    }

    private function make_directory($path) {
        if (!file_exists($path)) {
            mkdir($path, 0775);
            chmod($path, 0775);
        }
    }

}
