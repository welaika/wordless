<?php

class WordlessTestConfigurations {
    private $currentTheme;
    private $files = [
        'wpconfig' => [
            'template_path' => null,
            'destination_path' => null
        ],
        'gitlabci' => [
            'template_path' => null,
            'destination_path' => null
        ]
    ];

    public function __construct() {
        $this->currentTheme = get_option('current_theme');
        $this->files['wpconfig']['template_path'] = Wordless::join_paths(__DIR__ . '/templates/wp-config-test.php');
        $this->files['wpconfig']['destination_path'] = Wordless::join_paths(ABSPATH, 'wp-config.php');
        $this->files['gitlabci']['template_path'] = Wordless::join_paths(__DIR__ . '/templates/gitlab-ci.yml');
        $this->files['gitlabci']['destination_path'] = Wordless::join_paths(ABSPATH, '.gitlab-ci.yml');
    }

    public function install() : void {
        foreach ($this->files as $file => $props) {
            $source_content = file_get_contents($props['template_path']);
            $source_content = str_replace("%THEME_NAME%", $this->currentTheme, $source_content);
            $source_content = str_replace("%ABSPATH%", ABSPATH, $source_content);
            file_put_contents($props['destination_path'], $source_content);
            chmod($props['destination_path'], intval(0664, 8));
        }
    }
}
