<?php

// Mocking apply_filter. It works only with one argument ATM
function apply_filters($filterName, $arg){
  return $arg;
}
