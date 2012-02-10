<?php

# mocking WP bloginfo
function get_bloginfo($key) {
  if ($key == "template_url")
    return "http://mocked.url/wp-content/themes/mocked_theme";
  return "mocked_" . $key;
}
