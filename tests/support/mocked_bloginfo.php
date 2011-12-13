<?php

# mocking WP bloginfo
function get_bloginfo($key) {
  return "mocked_" . $key;
}
