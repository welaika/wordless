<?php

# mocking WP get_theme_data()
function get_theme_data($theme_style_file) {
  return array(
    "Name" => "Wordless",
    "URI" => '',
    "Description" => "This is a vanilla Wordless theme. Use Haml, Sass and Coffescript, and make web development fun again :)",
    "Author" => 'weLaika',
    "AuthorURI" => 'http://welaika.com/',
    "Version" => '1.0',
    "Template" => '',
    "Status" => 'publish',
    "Tags" => array(),
    "Title" => 'Wordless',
    "AuthorName" => 'weLaika'
  );
}
