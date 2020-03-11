<?php

if ( ! class_exists( 'WP_CLI' ) ) {
    return;
}

/**
* Manage wordless themes
*/
class WordlessCommand {
    /**
    * Create a new Wordless theme
    *
    * ## OPTIONS
    *
    * <name>
    * : Gives your theme a name
    *
    * @param string $args Name of the theme
    * @return void
    */
    public function create($args) {
        $theme = $args[0];
        $cli_command_args =  array(
            'launch' => true,
            'return' => true );

            $builder = new WordlessThemeBuilder($theme, $theme, intval(0664, 8));
            $builder->build();

            WP_CLI::line(
                WP_CLI::runcommand( "theme activate $theme", $cli_command_args )
            );

            WP_CLI::line(
                WP_CLI::runcommand( "theme status $theme", $cli_command_args )
        );
    }

    /**
    * Clear theme's `tmp` folder
    *
    * @return void
    */
    public function clear_tmp() {
        Wordless::clear_theme_temp_path();

        if (count($this->temp_files()) > 0) {
            WP_CLI::error('Cannot delete all the files inside `tmp` folder');
            return false;
        } else {
            WP_CLI::success('`tmp` folder cleared');
            return true;
        }
    }

    /**
    * Return the list of files in theme's `tmp` directory
    *
    * @return Array
    */
    private function temp_files() {
        return Wordless::recursive_glob(Wordless::theme_temp_path());
    }
}

$instance = new WordlessCommand;
WP_CLI::add_command( 'wordless theme', $instance );
