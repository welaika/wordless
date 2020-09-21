<?php

# This is a wp-config.php ready for codeception test suites

if ( getenv( 'CI' )) {
    define( 'DB_NAME', '%THEME_NAME%_test' );
    define(' WP_CACHE', false );
    define( 'DB_USER', 'root' );
    define( 'DB_PASSWORD', 'mysql' );
    define( 'DB_HOST', 'mysql' );
} else {
    if (
        // Custom header.
        isset( $_SERVER['HTTP_X_TESTING'] )
        // Custom user agent.
        || ( isset( $_SERVER['HTTP_USER_AGENT'] ) && $_SERVER['HTTP_USER_AGENT'] === 'wp-browser' )
        // The env var set by the WPClIr or WordPress modules.
        || getenv( 'WPBROWSER_HOST_REQUEST' )
        || getenv( 'WP_ENV' ) === 'test'
        ) {
            // Use the test database if the request comes from a test.
            define( 'DB_NAME', '%THEME_NAME%_test' );
            // Disable cache if you have cache plugins enabled
            define('WP_CACHE', false);
        } else {
        // Else use the default one.
        define( 'DB_NAME', '%THEME_NAME%' );
    }

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

$table_prefix = 'wp_';

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
