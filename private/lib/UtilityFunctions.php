<?php

/**
 * Determines whether the given array is an associate array.
 */
function is_associative_array($value) {
  if (!is_array($value)) {
    return false;
  }
  
  foreach (array_keys($value) as $key) {
    if (!is_numeric($key)) {
      return true;
    }
  }
  
  return false;
}

/**
 * Filters an array, removing the list of supplied keys.
 */
function array_reject_keys($array, $keys) {
  $keys = array_flip($keys);
  return array_diff_key($array, $keys);
}

/**
 * Properly massages values for INSERTs or UPDATEs.
 */
function massage_db_value($value) {
  if (is_numeric($value)) {
    return $value;
  } elseif (is_string($value)) {
    return "'" . mysql_real_escape_string($value) . "'";
  } elseif (is_null($value)) {
    return 'NULL';
  }
}

/**
 * Quick, don't look at this code!  It's been shown to cause blindness in lab rats and small children!
 * It's just an ugly work around to try to save PHP from its own broken self.
 * You're better off not knowing how it works; trust me.
 */
function get_static_class() {
  $trace = debug_backtrace();
  $trace = $trace[1];
  $func  = $trace['function'];
  $lines = file($trace['file'], FILE_IGNORE_NEW_LINES);
  $class = preg_replace("/^.*?[\s(]?(\w+)::$func\(.*$/", '\1', $lines[$trace['line'] - 1]);
  return $class;
}

?>