<?php

if ( ! class_exists( 'WP_CLI' ) ) {
    return;
}

/**
* Manage wordless themes
*/
class WordlessCommand {
    /**
    * Upgrade current active Wordless theme to WebPack.
    * Does nothnig if the active theme is not a Wordless one.
    *
    * ## OPTIONS
    *
    * [--force]
    * : Force the upgrade of the theme. Useful when
    * the webpack configuration was updated in the plugin
    *
    * @return void
    */
    public function upgrade($args, $assoc_args) {
        if ( $this->theme_is_upgradable() ) {
            WP_CLI::warning('Theme is not Wordless2 ready. Going ahead upgrading...');

            $builder = new WordlessThemeBuilder(null, null, intval(0664, 8));
            if ( $builder->upgrade_to_webpack() )
            WP_CLI::success( 'Theme succesfully upgraded. Now you can getting started with Wordless2 (https://github.com/welaika/wordless#getting-started)' );
            else
            WP_CLI::error( 'Sorry, something went wrong during theme upgarde.' );
        } elseif ($assoc_args['force']) {
            WP_CLI::warning('Forcing the upgrade...');

            $builder = new WordlessThemeBuilder(null, null, intval(0664, 8));
            if ( $builder->upgrade_to_webpack() )
            WP_CLI::success( 'Theme succesfully upgraded. Now you can getting started with Wordless2 (https://github.com/welaika/wordless#getting-started)' );
            else
            WP_CLI::error( 'Sorry, something went wrong during theme upgarde.' );
        } else {
            WP_CLI::error('Theme is already Wordless2 ready or is not a Wordless theme');
            return;
        }
    }

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
        * Check if current theme is Wordless compatible and ready for WebPack
        *
        * @return void
        */
        public function is_wordless2_ready() {
            if ( $this->theme_is_upgradable() )
                WP_CLI::error('Nope');
            else
                WP_CLI::success('Yep!');
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

        private function theme_is_upgradable() {
            return !Wordless::theme_is_webpack_ready();
        }
    }

$instance = new WordlessCommand;
WP_CLI::add_command( 'wordless theme', $instance );
