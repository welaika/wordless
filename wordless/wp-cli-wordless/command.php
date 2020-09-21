<?php

if ( ! class_exists( 'WP_CLI' ) ) {
    return;
}

/**
* Manage wordless themes
*/
class WordlessCommand {
    /**
    * Create and acrivate a new Wordless theme with the given name
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
    * Upgrade current active Wordless theme.
    *
    * From the Wordless perspective an _upgrade_ means to upgrade
    * the build stack and build configurations.
    * New configurations will be copied from the starter theme shipped
    * with the Wordless plugin into your current theme.
    *
    * This operation may broke your theme build, so be careful: make a
    * backup of your theme or be sure to have a clean GiT state thus
    * a simple reset/checkout will eventually save you.
    *
    * After the updgrade remember to launch `yarn install` in order
    * to update `node_modules` based on new pacjage.json.
    *
    * Newer configuration may require you to install an updated node
    * version too.
    *
    * @return void
    */
    public function upgrade($args, $assoc_args) {
        if (!Wordless::theme_is_upgradable()) {
            WP_CLI::error_multi_line([
                'It seems you are using a theme created with a Wordless verion < 2',
                'or maybe you have heavily customized the theme folder structure.',
                'We can\'t afford to make an automatic upgrade for you, sorry'
            ]);

            WP_CLI::halt(1);
        }

        WP_CLI::warning('Going to copy following files into your theme: ' . join(array_values(Wordless::$webpack_files_names), ', '));

        WP_CLI::confirm('This is a potentially destructive operation. Do you have a backup and would like to proceed?');

        $builder = new WordlessThemeBuilder(null, null, intval(0664, 8));

        if ( $builder->upgrade_theme_config() ) {
            WP_CLI::success( 'Theme succesfully upgraded. Now you can getting started with Wordless2 (https://wordless.readthedocs.io)' );
            WP_CLI::log('Remember to run `yarn install` to update node_modules based on new configuration');
        } else {
            WP_CLI::error( 'Sorry, something went wrong during theme upgarde.' );
        }
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
    * Update wp-config.php to support test suite and .gitlab-ci.yml for CI into project root.
    *
    * @return void
    */
    public function setup_test_suite() {
        $testConfigs = new WordlessTestConfigurations();
        $testConfigs->install();

        WP_CLI::success('Test configuration installed');

        // NOTE: We're not managing error cases
        return true;
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
