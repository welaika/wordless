<?php

# mocking WP get_template_directory()
function get_template_directory() {
  return "/mocked/file/path/to/mocked_root/mocked_theme";
}
