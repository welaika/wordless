<?php

# mocking WP bloginfo
function bloginfo($key) {
  return "mocked_" . $key;
}

function get_bloginfo($key) {
  return "mocked_" . $key;
}
