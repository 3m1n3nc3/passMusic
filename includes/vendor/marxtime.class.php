<?php 
/**
 * Time management and calculation class, written by 3m1n3nc3
 */
class marxTime
{   
    public $time; 

    /**
     * Time Difference function
     */     
    function timeAgo($time, $x=0)
    {
        // Use strtotime() function to convert your time stamps before sending to the plugin
        if (isset($this->time)) {
            $time = strtotime($this->time); 
        } else {
            $time = strtotime($time);
        }
        $time_difference = time() - $time;

        if($time_difference < 1) { 
            return 'Less than 1 second ago'; 
        }

        $seconds = array( 
            12 * 30 * 24 * 60 * 60  =>  'year',
            30 * 24 * 60 * 60       =>  'month',
            24 * 60 * 60            =>  'day',
            60 * 60                 =>  'hour',
            60                      =>  'minute',
            1                       =>  'second', 
           -1                       =>  'millisecond' 
        );

        foreach( $seconds as $secs => $ret )
        {
            $diff = $time_difference / $secs;

            if( $diff >= 1 )
            {
                $t = round( $diff );
                $y = $ret == 'hour' || $ret == 'minute' || $ret == 'second' || $ret == 'millisecond' ? true : false;
                // Check the request type
                if ($x == 1) {
                    if ((date('d', time()) - date('d', $time)) == 1) {
                        // If the time is been more than a day but less than two show yesterday
                        return date('h:i A', $time).' | Yesterday'; 
                    } elseif ($ret == 'year') {
                        // If the time is been up to a year show full year
                        return date('h:i A', $time).' | '.date('F j Y', $time); 
                    } elseif ($y) {
                        // If the time is been less than or equal to a day show today
                        if ($ret == 'second') {
                            return 'A moment ago';
                        }
                        return date('h:i A', $time).' | Today'; 
                    } else {
                        // If the time is been more than two days show the date
                        return date('h:i A', $time).' | '.date('F j', $time); 
                    }                   
                } elseif ($x == 2) {
                    // Show only date
                    if ($ret == 'year' && $t==1) {
                        // If the time is been more than a day but less than two show yesterday
                        return date('M j Y', $time);
                    } else {
                        return date('M j', $time);
                    }
                } else {
                    return 'About ' . $t . ' ' . $ret . ( $t > 1 ? 's' : '' ) . ' ago';
                }
            }
        }
    }

    // Hugh number formatter  
    function numberFormater($n, $full = 0, $precision = 1) {
        if ($full == 1) {
            $n_format = number_format($n);
            $suffix = '';
        } elseif ($n < 900) {
            // 0 - 900
            $n_format = number_format($n, $precision);
            $suffix = '';
        } else if ($n < 900000) {
            // 0.9k-850k
            $n_format = number_format($n / 1000, $precision);
            $suffix = 'K';
        } else if ($n < 900000000) {
            // 0.9m-850m
            $n_format = number_format($n / 1000000, $precision);
            $suffix = 'M';
        } else if ($n < 900000000000) {
            // 0.9b-850b
            $n_format = number_format($n / 1000000000, $precision);
            $suffix = 'B';
        } else {
            // 0.9t+
            $n_format = number_format($n / 1000000000000, $precision);
            $suffix = 'T';
        }
          // Remove unnecessary zeros after decimal. "1.0" -> "1"; "1.00" -> "1"
          // Intentionally does not affect partials, eg "1.50" -> "1.50"
        if ( $precision > 0 ) {
            $dotzero = '.' . str_repeat( '0', $precision );
            $n_format = str_replace( $dotzero, '', $n_format );
        }
        
        return $n_format . $suffix;
    }

    function swissConverter($value, $precision = 2, $format = true) {
        //Below converts value into bytes depending on input (specify mb, for 
        //example)
        $bytes = preg_replace_callback('/^\s*(\d+)\s*(?:([kmgt]?)b?)?\s*$/i', 
        function ($m) {
            switch (strtolower($m[2])) {
              case 't': $m[1] *= 1024;
              case 'g': $m[1] *= 1024;
              case 'm': $m[1] *= 1024;
              case 'k': $m[1] *= 1024;
            }
            return $m[1];
            }, $value);
        if(is_numeric($bytes)) {
            if($format === true) {
                //Below converts bytes into proper formatting (human readable 
                //basically)
                $base = log($bytes, 1024);
                $suffixes = array('', 'KB', 'MB', 'GB', 'TB');   

                return round(pow(1024, $base - floor($base)), $precision) .' '. 
                         $suffixes[floor($base)];
            } else {
                return $bytes;
            }
        } else {
            return NULL; //Change to preferred response
        }
    }

    /**
    * Replace characters in a string
    */
    function reconstructString($string = '') {
        /**
        * To change the default separator to explode set $this->explode = separator.
        * To change the default string to search and replace set $this->find = string.
        * To change the default string used to replace the search string set $this->replace = string.
        * By default the function returns only the first two array values after exploding the string if it finds more
        * than one, other wise it will return just one.
        * To change this behavior set $this->part = key to the array key you want the value returned for you.
        **/
        if (isset($this->explode)) {
            $explode = $this->explode;
        } else {
            $explode = '-';
        }

        if (isset($this->find)) {
            $find = $this->find;
        } else {
            $find = '_';
        }

        if (isset($this->replace)) {
            $replace = $this->replace;
        } else {
            $replace = ' ';
        }
        $filtered_string = str_replace($find, $replace, $string);

        if (isset($this->get_array)) {
            return explode($explode, trim($filtered_string));
        } else {
            if (stripos($string, $explode)) {
                $filtered_string = explode($explode, $filtered_string);
                
                if (isset($this->part)) {
                    $new_string = $filtered_string[$this->part];
                } else {
                    if (count($filtered_string) > 1) {
                        $new_string = $filtered_string[0].' '.$filtered_string[1];
                    } else {
                        $new_string = $filtered_string[0];
                    }
                }
            } else {
                $new_string = $filtered_string;
            }
        }

        return $new_string;
    }

    // Time to go function
    function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' ) {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        
        $interval = date_diff($datetime1, $datetime2);
        
        return $interval->format($differenceFormat);
        
    }

    // Event Time remaining
    function time2go($event_date, $event_time) {
        $today = date("Y-m-d h:i:s");
        $event_dt = $event_date;
        $event_tm = $event_time;
        $event = date('Y-m-d h:i:s', strtotime("$event_dt $event_tm"));
        $echo = dateDifference($today , $event , $differenceFormat = '%a Days, %h Hours, %i Minutes to go!' );
        return $echo;
    }

    function timeSub($date, $today = 'today') {
        $do = isset($this->do) ? $this->do : '-';
        $date = date('Y-m-d', strtotime($today.$do.$date));
        return $date;
    }
    // Combine two different date and time string to make a datetime stamp
    function timemerger($date, $time, $type = null) {
        if ($type) {
            $date = new DateTime($date);
            $time = new DateTime($time);

            //merge objects to new object:
            $merge = new DateTime($date->format('Y-m-d') .' ' .$time->format('H:i:s'));
            return $merge->format('Y-m-d H:i:s');       
        } else {
            $event_dt = $date;
            $event_tm = $time;
            $event = date('Y-m-d h:i:s', strtotime("$event_dt $event_tm"));  
            return $event;
        }
    }

    function get_percentage($event_date, $event_time) {
        $today = date("Y-m-d h:i:s");
        $event_dt = $event_date;
        $event_tm = $event_time;
        $event = date('Y-m-d h:i:s', strtotime("$event_dt $event_tm"));
        $swap_diff = dateDifference($event, $today);
        $diff_swap = dateDifference($today, $event);

        return ($diff_swap / $swap_diff) * 100.0;
    }

    function date_progress($start, $end, $today = null) {
        $date = $date ?: time();
        return (($date - $start) / ($end - $start)) * 100;
    }

    /**
     * Format a date with the PHP date function
     * @param  [type]  $date [description]
     * @param  integer $type defines the type of format to give the provided date
     * @return [type]        [description]
     */
    function dateFormat($date, $type = 0) {
        $d=strtotime($date);

        if ($type == 0) {
            $time = date("D M d - h:i:s A", $d);
        } elseif ($type == 1) {
            $time = date("d/m/Y, h:i A", $d);
        } elseif ($type == 2) {
            $time = date("d/m/Y", $d);
        } elseif ($type == 3) {
            $time = date("F d, Y", $d);
        } elseif ($type == 4) {
            $time = date("F d, Y - h:i A", $d);
        }
        return $time;
    }

    function percenter($number, $total) {
        $n = ($number * 100) / $total;
        return $this->numberFormater($n, 1);
    }
    
    function evenOdd($number){ 
        if($number % 2 == 0){ 
            return 1;
        } else{ 
            return 0;
        } 
    }

    function yearMonthlyArray($fetch = null, $date = null) {
        if ($date) {
            $date = date('d-Y', strtotime($date));
        } else {
            $date = date('d-Y', strtotime('today'));
            $date = explode('-', $date);
        }

        $archiver = array(
            '1'   => array_merge(array('january'), $date), 
            '2'   => array_merge(array('february'), $date), 
            '3'   => array_merge(array('march'), $date), 
            '4'   => array_merge(array('april'), $date),
            '5'   => array_merge(array('may'), $date),
            '6'   => array_merge(array('june'), $date),
            '7'   => array_merge(array('july'), $date),
            '8'   => array_merge(array('august'), $date),
            '9'   => array_merge(array('september'), $date),
            '10'  => array_merge(array('october'), $date),
            '11'  => array_merge(array('november'), $date),
            '12'  => array_merge(array('december'), $date)
        );
        if ($fetch) {
           $set = $archiver[$fetch];
        } else {
            $set = $archiver;
        }
        return $set;
    }

    /**
     * Converts a 2 dimensional array to an associative array 
     * @param  array  $array this is the array to convert
     * @return array        the associative array to return
     */
    function dekeyArray($array = []) {
        $arr = [];
        foreach ($array as $k => $v) {
            $arr[] .= $v['name'];
        }    
        return $arr;    
    }
}
$marxTime = $mxtm = new marxTime;
?>
