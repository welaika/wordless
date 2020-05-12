<?php

// Mocking apply_filter. It works only with one argument ATM
function apply_filters( $filterName, $arg ) {
	return $arg;
}

function add_action( $filterName, $arg ) {
}

function plugin_i18n() {

}

add_action( 'init', 'plugin_i18n' );
