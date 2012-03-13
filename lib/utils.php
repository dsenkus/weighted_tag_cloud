<?php
class Utils {
	/**
	 * Log text
	 * @param string $string text to log
	 * @return string
	 */
  public static function log($string){
    echo $string . "\n";
  }

  public static function array_pos($needle, $haystack){
    $count = 0;
    foreach($haystack as $key => $value){
      if($key == $needle){
        return $count;
      } 

      $count++;
    }

    return false;
  }
}
