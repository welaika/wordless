<?php
/**
 * Provides methods for handling dates and times in a user-friendly
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
            return __('less than a minute', "we");
          } else if ($dm >= 1 && $dm < 2) {
            return __('1 minute', "we");
          }
        } else {
          switch (true) {
            case $ds >= 0 && $ds <= 4:
              return __('less than 5 seconds', "we");
              break;
            case $ds >= 5 && $ds <= 9:
              return __('less than 10 seconds', "we");
              break;
            case $ds >= 10 && $ds <= 19:
              return __('less than 20 seconds', "we");
              break;
            case $ds >= 20 && $ds <= 39:
              return __('half a minute', "we");
              break;
            case $ds >= 40 && $ds <= 59:
              return __('less than a minute', "we");
              break;
            default:
              return __('less than a minute', "we");
            break;
          }
        }
        break;
      case $dm >= 2 && $dm <= 44:
        return sprintf(__("%d minutes", "we"), round($dm));
        break;
      case $dm >= 45 && $dm <= 89:
        return __('about 1 hour', "we");
        break;
      case $dm >= 90 && $dm <= 1439:
        return sprintf(__("about %d hours", "we"), round($dm / 60.0));
        break;
      case $dm >= 1440 && $dm <= 2879:
        return __('1 day', "we");
        break;
      case $dm >= 2880 && $dm <= 43199:
        return sprintf(__("%d days", "we"), round($dm / 1440));
        break;
      case $dm >= 43200 && $dm <= 86399:
        return __('about 1 month', "we");
        break;
      case $dm >= 86400 && $dm <= 525599:
        return sprintf(__("%d months", "we"), round($dm / 43200));
        break;
      case $dm >= 525600 && $dm <= 1051199:
        return __('about 1 year', "we");
        break;
      default:
        return sprintf(__("over %d years", "we"), round($dm / 525600));
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
    $date = current_time('timestamp');
    $difference = time() - $date;
    $periods = array(
      315360000 => array(__('decade', "we"), __('decades', "we")),
      31536000 => array(__('year', "we"), __('years', "we")),
      2628000 => array(__('month', "we"), __('months', "we")),
      604800 => array(__('week', "we"), __('weeks', "we")),
      86400 => array(__('day', "we"), __('days', "we")),
      3600 => array(__('hour', "we"), __('hours', "we")),
      60 => array(__('minute', "we"), __('minutes', "we")),
      1 => array(__('second', "we"), __('seconds', "we"))
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
    return sprintf(__("%s ago", "we"), $retval);
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
