<?php

if (!defined('APP_ENV')) {
  define('APP_ENV', 'main');
}
define('APP_ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..');
define('VIEWS', APP_ROOT . DIRECTORY_SEPARATOR . 'app' .  DIRECTORY_SEPARATOR . 'views');
define('STOP_RENDERING', 1);

ini_set('date.timezone', 'America/Los_Angeles');

function __autoload($class_name) {
  if (in_array($class_name, get_declared_classes())) { return; }
  
  $paths = split(PATH_SEPARATOR, get_include_path());
  foreach ($paths as $path) {
      $fullpath = $path . DIRECTORY_SEPARATOR . $class_name . '.php';
      if (file_exists($fullpath)) {
          require_once $fullpath;
          return;
      }
  }
  
  eval("class $class_name {};"); // Stave off the fatal error.
  throw new Exception("Could not load class $class_name");
}

function __push_include_path($new_path) {
  if (preg_match("/^\./", $new_path)) {
    $new_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $new_path;
  }
  set_include_path(get_include_path() . PATH_SEPARATOR . $new_path);
}

function include_helpers() {
  include_once 'helpers.php';
}

function include_view($_view, array $_data = array()) {
  include_helpers();
  extract($_data);
  include VIEWS . DIRECTORY_SEPARATOR . $_view;
}

/**
 * Renders a given view; can only be called once per request.
 */
function render($view, $data = array()) {
  if (defined('__CONTENT__')) {
    throw new Exception('#render has already been called!');
  }
  
  ob_start();
  include_view($view, $data);
  define('__CONTENT__', ob_get_contents());
  ob_end_clean();
}

/**
 * Renders a given partial.
 * Partials are fragments of HTML and PHP, with filenames beginning with an underscore.
 * The $partial value expected is filename, without leading underscore or file extension, if the
 * partial exists in the current view directory, or its path relative to the views directory otherwise.
 *
 * E.g.:
 *   'partial'        # => '_partial.php'
 *   'shared/partial' # => 'shared/_partial.php'
 */
function render_partial($partial, $data = array()) {
  $sep = '\\' . DIRECTORY_SEPARATOR;
  $partial = preg_replace("/([^\/]+)$/", '_\1', $partial);
  if (preg_match("/\//", $partial)) {
    include_view("$partial.php", $data);
  } else {
    $matches = array();
    $stack = debug_backtrace();
    while ($stack && $frame = array_shift($stack)) {
      preg_match("/app${sep}views${sep}([^${sep}]+)/", $frame['file'], $matches);
      if ($matches[1]) { break; }
    }
    include_view($matches[1] . DIRECTORY_SEPARATOR . "$partial.php", $data);
  }
}

// Add our support directories to the include path
__push_include_path("../lib");
__push_include_path("../app/controllers");
__push_include_path("../app/models");
__push_include_path("../app/modules");

// Load the utility functions
require_once 'UtilityFunctions.php';

class Config {
  public $database;
}
$config = new Config();

// Load the database configuration
if (is_file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'databaseConfig.php')) {
  require_once('databaseConfig.php');
} else {
  include_view('config/missingDatabaseConfig.php');
  die;
}

global $database;
$database = new DatabaseConnection($config->database);

require_once 'routes.php';

?>