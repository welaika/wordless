<?php

class DateHelper {


  /**
   *
   * Original author: James Rose
   * @param int $from_time timestamp
   * @param int $to_time timestamp
   * @param bool $include_seconds
   * @return type
   */
  function distance_of_time_in_words($from_time,$to_time = 0, $include_seconds = false) {
    $dm = $distance_in_minutes = abs(($from_time - $to_time))/60;
    $ds = $distance_in_seconds = abs(($from_time - $to_time));

    switch ($distance_in_minutes) {
      case $dm > 0 && $dm < 1:
      if($include_seconds == false) {
        if ($dm == 0) {
          return 'less than a minute';
        } else {
          return '1 minute';
        }
      } else {
        switch ($distance_of_seconds) {
          case $ds > 0 && $ds < 4:
            return 'less than 5 seconds';
            break;
          case $ds > 5 && $ds < 9:
            return 'less than 10 seconds';
            break;
          case $ds > 10 && $ds < 19:
            return 'less than 20 seconds';
            break;
          case $ds > 20 && $ds < 39:
            return 'half a minute';
            break;
          case $ds > 40 && $ds < 59:
            return 'less than a minute';
            break;
          default:
            return '1 minute';
          break;
        }
      }
      break;
      case $dm > 2 && $dm < 44:
        return round($dm) . ' minutes';
        break;
      case $dm > 45 && $dm < 89:
        return 'about 1 hour';
        break;
      case $dm > 90 && $dm < 1439:
        return 'about ' . round($dm / 60.0) . ' hours';
        break;
      case $dm > 1440 && $dm < 2879:
        return '1 day';
        break;
      case $dm > 2880 && $dm < 43199:
        return round($dm / 1440) . ' days';
        break;
      case $dm > 43200 && $dm < 86399:
        return 'about 1 month';
        break;
      case $dm > 86400 && $dm < 525599:
        return round($dm / 43200) . ' months';
        break;
      case $dm > 525600 && $dm < 1051199:
        return 'about 1 year';
        break;
      default:
        return 'over ' . round($dm / 525600) . ' years';
      break;
    }
  }

  function get_the_time_ago($granularity=1) {
    $date = intval(get_the_date('U'));
    $difference = time() - $date;
    $periods = array(
      315360000 => array('decade', 'decades'),
      31536000 => array('year', 'years'),
      2628000 => array('month', 'months'),
      604800 => array('week', 'weeks'),
      86400 => array('day', 'days'),
      3600 => array('hour', 'hours'),
      60 => array('minute', 'minutes'),
      1 => array('second', 'seconds')
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
    return $retval.' ago';
  }

  function time_tag($date_or_time = NULL , $text = NULL, $attributes = array()) {
    $date_or_time = $date_or_time ? date(DATE_W3C, $date_or_time) : date(DATE_W3C)
    $options  = array( "datetime" => $date_or_time );
    $text = $text ? $text : strftime("%F", $date_or_time)
    $options = array_merge($options, $attributes);
    return content_tag("time", $text, $options);
  }
}

Wordless::register_helper("DateHelper");
