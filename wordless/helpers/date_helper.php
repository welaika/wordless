<?php

function get_the_time_ago($granularity=1) {
  $date = intval(get_the_date('U'));
  $difference = time() - $date;
  $periods = array(
    315360000 => array('decennio', 'decenni'),
    31536000 => array('anno', 'anni'),
    2628000 => array('mese', 'mesi'),
    604800 => array('settimana', 'settimane'),
    86400 => array('giorno', 'giorni'),
    3600 => array('ora', 'ore'),
    60 => array('minuto', 'minuti'),
    1 => array('secondo', 'secondi')
  );

  foreach ($periods as $value => $key) {
    if ($difference >= $value) {
      $time = floor($difference/$value);
      $difference %= $value;
      $retval .= ($retval ? ' ' : '').$time.' ';
      $retval .= (($time > 1) ? $key[1]: $key[0]);
      $granularity--;
    }
    if ($granularity == '0') { break; }
  }
  return $retval.' fa';
}

