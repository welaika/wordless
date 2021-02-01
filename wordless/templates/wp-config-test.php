<?php

// This is a wp-config.php ready for codeception test suites

// In this if/else block we switch database configuration by
// environment: GitLab CI, local test and default.
// In production you can (could?) simplify this one a lot
// keeping only what's needed to actually run the site.
if ( getenv( 'CI' )) {
    // This section controls constant definition when running inside
    // Gitlab's CI. The `CI` env variable is setup by GitLab self.
    // Feel free to update it to fit your needs: following parameters
    // will work out of the box with the wordless shipped `.gitlab-ci.yml`
    define( 'DB_NAME', '%THEME_NAME%_test' );
    define(' WP_CACHE', false );
    define( 'DB_USER', 'root' );
    define( 'DB_PASSWORD', 'mysql' );
    define( 'DB_HOST', 'mysql' );
} else {
    if ( // Are we running the test suite locally? We do support 3 ways to deduce it and if we are,
        // then we'll change the database name
        //
        // Custom header.
        isset( $_SERVER['HTTP_X_TESTING'] )
        // Custom user agent.
        || ( isset( $_SERVER['HTTP_USER_AGENT'] ) && $_SERVER['HTTP_USER_AGENT'] === 'wp-browser' )
        // The env var set by WPBrowser modules
        || getenv( 'WPBROWSER_HOST_REQUEST' )
        // The env var set by our npm scripts
        || getenv( 'WP_ENV' ) === 'test'
    ) { // TEST definitions
        // Use the test database if the request comes from a test.
        define( 'DB_NAME', '%THEME_NAME%_test' );
        // Disable cache if you have cache plugins enabled
        define('WP_CACHE', false);
    } else { // DEVELOPMENT definitions
    // Default (development) db name
    define( 'DB_NAME', '%THEME_NAME%' );
}

    // We assume user, password and host will be the same in test and development. If it's not
    // true for your setup, then you can move following definitions into previous if/else block
    define( 'DB_USER', 'root' );
    define( 'DB_PASSWORD', '' );
    define( 'DB_HOST', 'localhost' );
}

define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',          'J*%Z|b>HIlRDZT`&Wi2xXlYyhz;jx*~2D{jZ<@p 9tw{It22[3Qv/@K$IhQgV]@]' );
define( 'SECURE_AUTH_KEY',   '-qQdjYnHDqu]Xav@>#M?<gxfIdzmD!7t$7cGg9&f(@3pLR$KJ{E,*3`;oR&::G;-' );
define( 'LOGGED_IN_KEY',     'N=lX}uW~:f-Y`Y@*f9wo-x,f@c,d_hrja(>SeQ=D@Pc%7KQqjqIDFAhV>@Ub3%6-' );
define( 'NONCE_KEY',         'nqh&9[gy1>%/iYFz*<HKobnUkbj6+=Dl_~!,jhB<Ae##!]V&*)y2C4[MRomR5.|,' );
define( 'AUTH_SALT',         'Ak=$f2no2LkXQlt28V+twhVo^- 4@mnu`[BWvdbjeTo2{H+qXbk}*a5=GClq>C+5' );
define( 'SECURE_AUTH_SALT',  '7,,2G&-)wOxv7:&kN$2`syd}qk1?ANs*K:`uAzUyOKBW6yyVmc_xXS8/eQ2FyEri' );
define( 'LOGGED_IN_SALT',    'OGE#-QvpF53=1BI95V+;j&bFk_3XWmZ8uQ>O->[N#2Ig]-rn:9;`w.b9uZgR^3SO' );
define( 'NONCE_SALT',        '@qWE4FyOft160*aM98D.$bT@Md9z(*(agJ2nfZcbY:f!P3=g-]tkycQvk2&oN(]~' );
define( 'WP_CACHE_KEY_SALT', 'Y)@uzFCw4v:QS^pknF1w0:zvv`f?,Y~IlY]&PB?NyrSQRAP[VC@r[h)?=G^@^BJl' );

define('ENVIRONMENT', 'development');
define('BYPASS_STATIC', false);

$table_prefix = 'wp_';

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
