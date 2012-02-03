<?php

class BaseController {
  static $filters = array();
  
  function dispatch($action, $params) {
    $class = get_class($this);
    $data = array();
    
    $before = $this->runFilters($action, $params, self::$filters["$class-before"]);
    if (!$before) { return; }
    $flag = $this->$action($params, $data);
    $after = $this->runFilters($action, $params, self::$filters["$class-after"]);
    if ($flag || !$after) { return; }
    
    return $data;
  }
  
  function raise_error($status, $message = NULL) {
    include_view('$status.php', array('message' => $message));
  }
  
  function is_get()    { return $_SERVER['REQUEST_METHOD'] == 'GET';    }
  function is_post()   { return $_SERVER['REQUEST_METHOD'] == 'POST';   }
  function is_put()    { return $_SERVER['REQUEST_METHOD'] == 'PUT';    }
  function is_delete() { return $_SERVER['REQUEST_METHOD'] == 'DELETE'; }
  
  private function runFilters($action, $params, $filters) {
    if ($filters) {
      foreach ($filters as $filter) {
        list($method, $options) = $filter;
        if (self::checkFilter($action, $options)) {
          $result = $this->$method($params);
          if (is_bool($result) && !$result) { return FALSE; }
        }
      }
    }
    return TRUE;
  }
  
  /**
   * Woah!  Returns true if the options have an 'only' that includes the $action, an 'except' 
   * that doesn't exclude the $action, or neither an 'only' or an 'except'.
   */
  private static function checkFilter($action, $options) {
    return
      ($options['only'] && 
        ((is_string($options['only']) && $options['only'] == $action) ||
        (is_array($options['only']) && in_array($action, $options['only'])))) ||
      ($options['except'] &&
        ((is_string($options['except']) && $options['except'] != $action) ||
        (is_array($options['except']) && !in_array($action, $options['except'])))) ||
      (!$options['only'] && !$options['except']);
  }
  
  /**
   */
  static function beforeFilter($method, $options = array()) {
    $class = get_static_class();
    $filters =& self::$filters["$class-before"];
    if (!$filters) { $filters = array(); }
    $filters[] = array($method, $options);
  }
  
  /**
   */
  static function afterFilter($method, $options = array()) {
    $class = get_static_class();
    $filters =& self::$filters["$class-after"];
    if (!$filters) { $filters = array(); }
    $filters[] = array($method, $options);
  }
}

?>