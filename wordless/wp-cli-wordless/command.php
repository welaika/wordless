<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

class WordlessCommand {
	public function upgrade($args, $assoc_args) {
		if ( $this->theme_is_upgradable() ) {
			WP_CLI::warning('Theme is not Wordless2 ready. Going ahead upgrading...');

			$builder = new WordlessThemeBuilder(null, null, intval(664, 8));

			if ( $builder->upgrade_to_webpack() )
				WP_CLI::success( 'Theme succesfully upgraded. Now you can getting started with Wordless2 (https://github.com/welaika/wordless#getting-started)' );
			else
				WP_CLI::error( 'Sorry, something went wrong during theme upgarde.' );
		} else {
			WP_CLI::error('Theme is already Wordless2 ready');
			return;
		}

	}

    private function theme_is_upgradable() {
        if (Wordless::theme_is_webpack_ready())
            return false;
        else
            return true;
    }
}

$instance = new WordlessCommand;
WP_CLI::add_command( 'wordless theme', $instance );
