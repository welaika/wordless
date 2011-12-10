<?php
/**
 * This module provides methods for handling dates and times in a user-friendly
 * way.
 * 
 * @ingroup helperclass
 */
class DateHelper {

  /**
   * Returns a user-friendly string representation of a time distance.
   * 
   * Given 2 timestamps, return a readable representation for their distance, 
   * link "1 minute" or "about an hour", etc.
   * 
   * @author: James Rose
   * 
   * @param int $from_time
   *   The timestamp for the initial time.
   * @param int $to_time
   *   (optional) The timestamp for the end time.
   * @param boolean $include_seconds 
   *   (optional) TRUE if you want to take  in account also seconds in the time
   *   distance ( must be set if you want information about a time distance 
   *   shorter than a minute ).
   * @return string
   *   A string representing the time distance.
   * 
   * @ingroup helperfunc
   */
  public function distance_of_time_in_words($from_time, $to_time, $include_seconds = false) {

    $dm = abs($from_time - $to_time) / 60;
    $ds = abs($from_time - $to_time);

    switch (true) {
      case $dm >= 0 && $dm < 2:
        if ($include_seconds == false) {
          if ($dm >= 0 && $dm < 1) {
            return 'less than a minute';
          } else if ($dm >= 1 && $dm < 2) {
            return '1 minute';
          }
        } else {
          switch (true) {
            case $ds >= 0 && $ds <= 4:
              return 'less than 5 seconds';
              break;
            case $ds >= 5 && $ds <= 9:
              return 'less than 10 seconds';
              break;
            case $ds >= 10 && $ds <= 19:
              return 'less than 20 seconds';
              break;
            case $ds >= 20 && $ds <= 39:
              return 'half a minute';
              break;
            case $ds >= 40 && $ds <= 59:
              return 'less than a minute';
              break;
            default:
              return 'less than a minute';
            break;
          }
        }
        break;
      case $dm >= 2 && $dm <= 44:
        return round($dm) . ' minutes';
        break;
      case $dm >= 45 && $dm <= 89:
        return 'about 1 hour';
        break;
      case $dm >= 90 && $dm <= 1439:
        return 'about ' . round($dm / 60.0) . ' hours';
        break;
      case $dm >= 1440 && $dm <= 2879:
        return '1 day';
        break;
      case $dm >= 2880 && $dm <= 43199:
        return round($dm / 1440) . ' days';
        break;
      case $dm >= 43200 && $dm <= 86399:
        return 'about 1 month';
        break;
      case $dm >= 86400 && $dm <= 525599:
        return round($dm / 43200) . ' months';
        break;
      case $dm >= 525600 && $dm <= 1051199:
        return 'about 1 year';
        break;
      default:
        return 'over ' . round($dm / 525600) . ' years';
      break;
    }
  }
  
  /**
   * Returns a 'time ago' string based on the creation time of the current post.
   * 
   * For more details on how this function works please check the get_the_date()
   * WordPress documentation.
   * 
   * @param int $granularity
   *   (optional)
   * @return string
   *   A string representation of the time elapsed since the creation of the
   *   current post.
   * 
   * @ingroup helperfunc
   * 
   * @see https://codex.wordpress.org/Function_Reference/get_the_date
   * 
   * @doubt I didn't get the use of $granularity!
   */
  public function get_the_time_ago($granularity = 1) {
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
        $retval .= ($retval ? ' ' : '') . $time . ' ';
        $retval .= (($time > 1) ? $key[1]: $key[0]);
        $granularity--;
      }
      if ($granularity == '0') { break; }
    }
    return $retval . ' ago';
  }

  /**
   * Builds a valid \<time /\> HTML tag.
   * 
   * @param string $date_or_time
   *   (optional) A valid timestamp representing a valid date or time objects.
   *   If no timestamp is passed, use the current date with the  @l{
   *   http://it.php.net/manual/en/class.datetime.php#datetime.constants.types,
   *   DATE_W3C} PHP predefined DateTime constant as date description.
   * @param string $text
   *   (optional) The text of the time HTML tag.
   * @param array $attributes
   *   (optional) An array of HTML attributes to be added to the rendered tag.
   * @return @e string
   *   A valid HTML \<time /\> tag.
   * 
   * @ingroup helperfunc
   * 
   * @see TagHelper::content_tag()
   */
  function time_tag($date_or_time = NULL, $text = NULL, $attributes = array()) {
    $date_or_time = $date_or_time ? date(DATE_W3C, $date_or_time) : date(DATE_W3C);
    $options  = array( "datetime" => $date_or_time );
    $text = $text ? $text : strftime("%F", $date_or_time);
    $options = array_merge($options, $attributes);
    return content_tag("time", $text, $options);
  }

  /**
   * Like distance_of_time_in_words() but with fixed value of $to_time.
   * 
   * This functions is a shorthand for distance_of_time_in_words($from_time, 
   * time(), $include_seconds).
   * 
   * @ingroup helperfunc
   * 
   * @see distance_of_time_in_words()
   */
  function time_ago_in_words($from_time, $include_seconds = false) {
    return distance_of_time_in_words($from_time, time(), $include_seconds);
  }
}

Wordless::register_helper("DateHelper");
