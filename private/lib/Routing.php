<?php

class Routing {
  static $routes = array();
  
  static function add($path, $options = array()) {
    array_push(self::$routes, array($path, $options));
  }
  
  static function routesTo($url) {
    $params = array_reject_keys($_REQUEST, array('controller', 'action'));
    $route = array();
    foreach (self::$routes as $r) {
      $match = self::matches($r, $url);
      if ($match) {
        $route = $match;
        break;
      }
    }
    return array_merge(array('action' => 'index'), $route, $params);
  }
  
  static function findRoute($map) {
    foreach (self::$routes as $route) {
      if (!array_diff_assoc($route[1], $map)) {
        $url = $route[0];
        foreach (array_diff_assoc($map, $route[1]) as $key => $value) {
          if (is_string($value) || is_numeric($value)) { $url = preg_replace("/:$key/", $value, $url); }
        }
        return $url;
      }
    }
  }
  
  private static function matches($route, $url) {
    $pattern = $route[0];
    $routeParts = split('/', $pattern);
    $requiredParts = $routeParts;
    $urlParts = split('/', $url);
    
    while (preg_match("/^:/", $requiredParts[count($requiredParts) - 1])) { array_pop($requiredParts); }
    if (count($requiredParts) > count($urlParts)) { return; }
    $parts = array();
    for ($i = 0; $i < count($urlParts); $i += 1) {
      if (preg_match("/^:/", $routeParts[$i])) {
        $parts[substr($routeParts[$i], 1)] = $urlParts[$i];
      } elseif ($routeParts[$i] != $urlParts[$i]) {
        return;
      }
    }
    return array_merge($route[1], $parts);
  }
}

?>