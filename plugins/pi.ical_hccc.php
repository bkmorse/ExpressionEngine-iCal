<?php 
  /**
  * ExpressionEngine Plugin
  *
  * @package  iCal HCCC
  * @subpackage Plugins
  * @category   Plugins
  * @author   Brad Morse
  * @link     http://github.com/bkmorse
  */

  $plugin_info = array(
  'pi_name'     => 'iCal HCCC',
  'pi_version'    => '1.0.0',
  'pi_author'     => 'Brad Morse',
  'pi_author_url'   => 'http://github.com/bkmorse',
  'pi_description'  => 'iCal feed for uPortal Calendar Portlet',
  'pi_usage'    => Ical_hccc::usage()
  );

class Ical_hccc {

  var $return_data;

  // -- Constructor -- //
  function ical_hccc() {
  
  global $TMPL, $FNS, $DB;

  $time_zone = ($TMPL->fetch_param('time_zone') !== false) ? $TMPL->fetch_param('time_zone') : "America/Dominica";
  $weblog_id = ($TMPL->fetch_param('weblog_id') !== false) ? $TMPL->fetch_param('weblog_id') : 192;
  $output = '';
  
  if($weblog_id):
    header("Content-Type: text/Calendar");
    header("Content-Disposition: inline; filename=ical.ics");
    date_default_timezone_set($time_zone);

    $weblog_title = $DB->query("
    SELECT
     blog_title
    FROM
     exp_weblogs
    WHERE
     weblog_id = ".$weblog_id."
    LIMIT 1
    ");
    
    $results = $DB->query("
    SELECT
     t.title, t.entry_id, t.entry_date, t.expiration_date, t.edit_date, 
     l.field_id_120 as location, l.field_id_12 as admissions_location,
     l.field_id_192 as community_ed_location
    FROM
     exp_weblog_titles t
    inner join
     exp_weblog_data l
    ON
     t.entry_id = l.entry_id
    where 
     t.weblog_id = ".$weblog_id."
    AND 
     (t.status = 'open' OR t.status = 'Homepage')
    AND
     t.expiration_date > UNIX_TIMESTAMP()
    ORDER BY
     t.entry_date DESC
    ");
    
    if($results->num_rows > 0):
    $output .= "BEGIN:VCALENDAR\r\n";
    $output .= "VERSION:2.0\r\n";
    $output .= "METHOD:PUBLISH\r\n";
    $output .= "X-WR-CALNAME:".$weblog_title->row['blog_title']."\r\n";
    $output .= "PRODID:-//Herkimer//iCal 5.0//EN\r\n";
    $output .= "CALSCALE:GREGORIAN\r\n";
    
    foreach($results->result as $r):

      $output .= "BEGIN:VEVENT\r\n";
      $output .= "UID:".$r['entry_id']."\r\n";
      $output .= "CREATED:".substr($r['edit_date'], 0, 8)."T".substr($r['edit_date'], 8, 13)."Z\r\n";
      $output .= "DTSTAMP:".substr($r['edit_date'], 0, 8)."T".substr($r['edit_date'], 8, 13)."Z\r\n";
      
      if($weblog_id == 42 || $weblog_id == 5) {
      $output .= "DTSTART:".date('Ymd', $r['entry_date'])."\r\n";
      $output .= "DTEND:".date('Ymd', $r['expiration_date'])."\r\n";
      } else {
      $output .= "DTSTART:".date('Ymd', $r['entry_date'])."T".date('His', $r['entry_date'])."Z\r\n";
      $output .= "DTEND:".date('Ymd', $r['expiration_date'])."T".date('His', $r['expiration_date'])."Z\r\n";
      }

      $output .= "TRANSP:TRANSPARENT\r\n";
      $output .= "SUMMARY:".$r['title']."\r\n";
      $output .= "LOCATION:".$r['location'].$r['admissions_location'].$r['community_ed_location']."\r\n";
      $output .= "SEQUENCE:3\r\n";
      $output .= "END:VEVENT\r\n";

    endforeach; //  foreach($results->result as $r)
    $output .= "END:VCALENDAR";
    endif;    //  if($results->num_rows > 0)
  endif;      //  if($weblogs)

  $this->return_data = $output;
  }
  /* END CONSTRUCTOR */

  // ----------------------------------------
  //  Plugin Usage
  // ----------------------------------------

  // This function describes how the plugin is used.
  //  Make sure and use output buffering

  function usage() {
  ob_start(); 
  ?>
  Use as follows:

  {exp:ical_hccc weblogs="12|2|3" time_zone="America/Dominica"}

  weblogs (required) = id's of each weblog
  time_zone (optional) = php timezone format, will default to America/Dominica - http://www.php.net/manual/en/timezones.php

  <?php
  $buffer = ob_get_contents();

  ob_end_clean(); 

  return $buffer;
  }
  /* END USAGE */
}
// END CLASS

/* End of file pi.link_icon.php */
/* Location: ./system/plugins/ical_hccc/pi.ical_hccc.php */
?>