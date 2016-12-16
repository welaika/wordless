<?php

# mocking WP get_template_directory()
function get_template_directory() {
    return dirname(__FILE__) . '/../fixtures/wordpress/wp-content/themes/mocked_theme';
}
